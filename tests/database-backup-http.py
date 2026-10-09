"""HTTP regression against a temporary database with real schema and synthetic data only."""
import os, pathlib, subprocess, tempfile, secrets, time, urllib.request, urllib.error, urllib.parse, http.cookiejar, re, json, zipfile, io, shutil
ROOT=pathlib.Path(__file__).resolve().parents[1]
PHP='C:/xampp/php/php.exe'
name='fsr_wizard_http_'+secrets.token_hex(6)
folder=pathlib.Path(tempfile.mkdtemp(prefix=name))
env=os.environ.copy()
env.update(FSR_DB_NAME=name,FSR_DB_USER='root',FSR_DB_PASSWORD='',FSR_DATA_PATH=str(folder/'data'),FSR_BACKUP_PATH=str(folder/'backups'))
setup=r'''
define('BASE_PATH',getcwd()); $c=require 'app/config/database.php';
$source=new PDO('mysql:host='.$c['host'].';port='.$c['port'].';dbname='.$c['database'],$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$target=new PDO('mysql:host='.$c['host'].';port='.$c['port'],'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$name=$argv[1]; if (!preg_match('/^fsr_wizard_http_[a-f0-9]{12}$/D',$name)) exit(1);
$target->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); $target->exec("USE `$name`"); $target->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($source->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) $target->exec($source->query("SHOW CREATE TABLE `$t`")->fetch(PDO::FETCH_NUM)[1]);
$target->exec('SET FOREIGN_KEY_CHECKS=1');
$s=$target->prepare("INSERT INTO users(full_name,username,email,password_hash,role,is_active,status) VALUES (?,?,?,?,?,1,'Active')");
foreach (['WizardAdmin'=>'System Admin','WizardViewer'=>'Read-Only User'] as $u=>$r) $s->execute(['Wizard test',$u,$u.'@example.invalid',password_hash('Wizard-test-password-2026',PASSWORD_DEFAULT),$r]);
$target->exec("INSERT INTO system_settings(setting_key,setting_value) VALUES ('maintenance_mode','0')");
'''
subprocess.run([PHP,'-r',setup,name],cwd=ROOT,check=True,capture_output=True)
server=None
BASE='http://127.0.0.1:18109/fsr/'
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args): return None
class Client:
    def __init__(self): self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()),NoRedirect())
    def request(self,path='',data=None,headers=None):
        if isinstance(data,dict): data=urllib.parse.urlencode(data).encode()
        try: response=self.opener.open(urllib.request.Request(BASE+path,data,headers or {}),timeout=90)
        except urllib.error.HTTPError as error: response=error
        return response.code,response.headers,response.read()
    def token(self):
        code,_,body=self.request(); assert code==200,(code,body[:200]); return re.search(rb'name="csrf_token" value="([a-f0-9]+)"',body).group(1).decode()
    def login(self,user):
        token=self.token(); code,_,_=self.request(data=dict(action='login',username=user,password='Wizard-test-password-2026',csrf_token=token)); assert code==302,code
        return self.token()
def passed(label): print('PASS:',label)
def sql(statement):
    code="define('BASE_PATH',getcwd());require 'app/Core/Database.php';App\\Core\\Database::connection()->exec($argv[1]);"
    subprocess.run([PHP,'-r',code,statement],cwd=ROOT,env=env,check=True,capture_output=True)
try:
    log=open(folder/'server.log','wb')
    server=subprocess.Popen([PHP,'-d','display_errors=0','-d','display_startup_errors=0','-d','upload_max_filesize=1M','-d','post_max_size=2M','-S','127.0.0.1:18109','-t',str(ROOT.parent)],cwd=ROOT,env=env,stdout=log,stderr=log,creationflags=subprocess.CREATE_NO_WINDOW)
    for _ in range(30):
        try: urllib.request.urlopen(BASE,timeout=2); break
        except Exception: time.sleep(.2)
    admin=Client(); token=admin.login('WizardAdmin')
    code,_,page=admin.request('index.php?page=system-maintenance&tab=database')
    assert code==200 and b'database-backup-wizard' in page and b'data-backup-action="upload"' in page
    assert page.count(b'name="csrf_token"')>=3
    passed('Administrator page renders export, upload, restore and CSRF-protected forms')
    code,_,_=admin.request(data=dict(action='database-backup-create',current_password='Wizard-test-password-2026'),headers={'X-Requested-With':'fetch'})
    assert code==419; passed('Missing CSRF is rejected')
    viewer=Client(); vt=viewer.login('WizardViewer')
    code,_,_=viewer.request(data=dict(action='database-backup-create',csrf_token=vt,current_password='Wizard-test-password-2026'))
    assert code==403; passed('Non-administrator cannot create or retrieve backups')
    payload=dict(action='database-backup-create',csrf_token=token,current_password='wrong')
    code,_,_=admin.request(data=payload); assert code==422
    passed('Current password is required')
    holder_code="define('DATA_PATH',getenv('FSR_DATA_PATH'));require 'app/Support/DatabaseMaintenanceGate.php';App\\Support\\DatabaseMaintenanceGate::exclusive();echo 'ready'.PHP_EOL;fflush(STDOUT);fgets(STDIN);"
    holder=subprocess.Popen([PHP,'-r',holder_code],cwd=ROOT,env=env,stdin=subprocess.PIPE,stdout=subprocess.PIPE,creationflags=subprocess.CREATE_NO_WINDOW)
    try:
        assert holder.stdout.readline().strip()==b'ready'
        assert admin.request()[0]==503
        passed('Exclusive operation lock blocks competing application requests')
    finally: holder.communicate(b'\n',timeout=10)

    payload['current_password']='Wizard-test-password-2026'
    code,_,body=admin.request(data=payload); assert code==200,(code,body)
    export=json.loads(body)['backup']; passed('HTTP export verifies all real application tables')
    code,headers,archive=admin.request(data=dict(payload,action='database-backup-download',backup_id=export['id'],format='zip'))
    assert code==200 and headers['Content-Type']=='application/zip'
    zipfile.ZipFile(io.BytesIO(archive)).testzip(); passed('Authenticated download delivers readable ZIP')
    code,_,sql_dump=admin.request(data=dict(payload,action='database-backup-download',backup_id=export['id'],format='sql'))
    assert code==200 and b'CREATE TABLE' in sql_dump; passed('Authenticated SQL download works')
    boundary='----WizardTest'+secrets.token_hex(10)
    parts=[]
    for key,value in dict(payload,action='database-backup-upload').items(): parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{value}\r\n'.encode())
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="backup_file"; filename="phpmyadmin.sql"\r\nContent-Type: application/sql\r\n\r\n'.encode()+sql_dump+b'\r\n')
    parts.append(f'--{boundary}--\r\n'.encode())
    code,_,body=admin.request(data=b''.join(parts),headers={'Content-Type':'multipart/form-data; boundary='+boundary})
    assert code==200,(code,body); staged=json.loads(body)['backup']; assert len(staged['table_summary'])==export['tables']
    passed('SQL upload validates all application table types and returns review counts')
    restore=dict(payload,action='database-backup-restore',backup_id=staged['id'],confirmation='RESTORE DATABASE',acknowledge='1')
    code,_,body=admin.request(data=restore); assert code==422 and b'maintenance' in body
    sql("UPDATE system_settings SET setting_value='1' WHERE setting_key='maintenance_mode'")
    code,_,body=admin.request(data=dict(restore,confirmation='wrong')); assert code==422
    passed('Maintenance and typed confirmation are mandatory')
    second=Client(); second_token=second.login('WizardAdmin')
    code,_,body=admin.request(data=restore); assert code==200,(code,body,(folder/'server.log').read_text(errors='replace')[-3000:])
    passed('HTTP restore succeeds with the complete application schema')
    code,_,body=second.request(data=dict(payload,csrf_token=second_token)); assert code==403
    passed('Restore invalidates another administrator session')
    code,_,_=second.request('index.php?page=system-maintenance&tab=database'); assert code==302
    passed('Normal page routes also reject pre-restore sessions')
    fresh=Client(); fresh.login('WizardAdmin')
    code,_,body=fresh.request('index.php?page=system-maintenance&tab=database'); assert code==200 and b'completed' in body and b'Recovery' in body
    passed('Restored administrator can sign in and see recovery history')
    code,_,body=fresh.request(data=b'x'*(2*1024*1024+1),headers={'Content-Type':'application/octet-stream','X-Requested-With':'fetch'})
    assert code==413,(code,body[:700]); passed('Oversized requests receive an understandable error')
    print('ALL HTTP WIZARD TESTS PASSED',flush=True)
    if os.environ.get('FSR_WIZARD_VISUAL') == '1': input('Visual test server ready at '+BASE+'; press Enter to clean up.\n')
finally:
    if server: server.terminate(); server.wait(timeout=10); log.close()
    cleanup="define('BASE_PATH',getcwd());$c=require 'app/config/database.php';$p=new PDO('mysql:host='.$c['host'].';port='.$c['port'],'root','');$n=$argv[1];if(preg_match('/^fsr_wizard_http_[a-f0-9]{12}$/D',$n))$p->exec(\"DROP DATABASE `$n`\");"
    subprocess.run([PHP,'-r',cleanup,name],cwd=ROOT,check=True,capture_output=True)
    shutil.rmtree(folder)
