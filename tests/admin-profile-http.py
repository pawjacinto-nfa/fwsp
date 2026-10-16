"""Run only with the isolated server/database configured by security-http.py."""
import importlib.util, pathlib, subprocess, json
spec = importlib.util.spec_from_file_location('helpers', pathlib.Path(__file__).with_name('security-http.py'))
h = importlib.util.module_from_spec(spec); spec.loader.exec_module(h)
def query(sql):
    return subprocess.run(['C:/xampp/mysql/bin/mysql.exe','--no-defaults','--host=127.0.0.1','--port=13306','--user=root',h.DB,'-N','-e',sql],check=True,capture_output=True,text=True).stdout.strip()
h.sql("UPDATE users SET role='System Admin',is_active=1 WHERE username='SECURITY_VERIFY_ADMIN'")
uid = query("SELECT id FROM users WHERE username='SECURITY_VERIFY_USER'")
url = 'index.php?page=user-profile&id='+uid
payload = {'action':'admin-user-profile','user_id':uid,'full_name':'Profile Updated','email':'profile-updated@example.invalid','contact_number':'09123456789','designation':'Profile test','office_scope':'field'}
anon=h.Client()
assert anon.request(url)[0]==302
assert anon.request(data={**payload,'csrf_token':anon.token()})[0]==302
user=h.Client(); user.login('SECURITY_VERIFY_USER')
assert user.request(url)[0]==302
assert user.request(data={**payload,'csrf_token':user.token()})[0]==302
assert query('SELECT full_name FROM users WHERE id='+uid) != 'Profile Updated'
h.passed('Anonymous and non-admin profile reads and writes denied')
admin=h.Client(); admin.login('SECURITY_VERIFY_ADMIN')
status,_,html=admin.request('index.php?page=users')
assert status==200 and 'page=user-profile&amp;id='+uid in html
status,_,html=admin.request(url)
assert status==200 and 'SECURITY_VERIFY_USER' in html and 'Save User Profile' in html
assert admin.request(data=payload,headers={'X-Requested-With':'fetch'})[0]==419
h.passed('Username links open profile page; CSRF enforced')
old_password=query('SELECT password_hash FROM users WHERE id='+uid)
h.sql("UPDATE users SET password_reset_status='EmailPending' WHERE id="+uid)
assert admin.request(data={**payload,'csrf_token':admin.token(),'role':'System Admin','password':'unauthorized-change'})[0]==302
assert query('SELECT full_name FROM users WHERE id='+uid)=='Profile Updated'
assert query('SELECT email FROM users WHERE id='+uid)=='profile-updated@example.invalid'
assert query('SELECT role FROM users WHERE id='+uid)!='System Admin'
assert query('SELECT password_hash FROM users WHERE id='+uid)==old_password
assert query('SELECT password_reset_status IS NULL FROM users WHERE id='+uid)=='1'
h.passed('Profile saved; password and role preserved; email change revokes reset')
for bad in [{'email':'invalid'}, {'email':'SECURITY_VERIFY_ADMIN@example.invalid'}, {'region_id':'999999999'}]:
    assert admin.request(data={**payload,**bad,'csrf_token':admin.token()})[0]==302
    assert query('SELECT email FROM users WHERE id='+uid)=='profile-updated@example.invalid'
assert admin.request('index.php?page=user-profile&id=999999999')[0]==302
h.passed('Invalid email, duplicate email, invalid location and missing user handled')
# Save an authenticated rendered page for visual inspection, without session cookies.
html=admin.request(url)[2]
html=html.replace('<head>','<head><base href="http://127.0.0.1:18089/">',1)
pathlib.Path('C:/xampp/htdocs/fsr/output/admin-profile-preview.html').write_text(html,encoding='utf-8')
h.sql("UPDATE users SET full_name='Security test',email='SECURITY_VERIFY_USER@example.invalid',designation=NULL,contact_number=NULL WHERE id="+uid)
