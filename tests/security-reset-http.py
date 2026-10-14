"""Controller-level reset checks using synthetic accounts in the isolated test database."""
import importlib.util, pathlib, re, secrets, subprocess, json, os
spec=importlib.util.spec_from_file_location('http_helpers', pathlib.Path(__file__).with_name('security-http.py'))
h=importlib.util.module_from_spec(spec); spec.loader.exec_module(h)
h.sql("UPDATE users SET role='System Admin' WHERE username='SECURITY_VERIFY_ADMIN'")
username=str(secrets.randbelow(100000)+900000)
# A collision fails the insert; no existing account is modified.
h.sql("INSERT INTO users (full_name,username,email,password_hash,role,is_active,status) VALUES ('Reset verification','"+username+"','reset-"+username+"@example.invalid','unused-test-hash','Read-Only User',1,'Active')")
result=subprocess.run(['C:/xampp/mysql/bin/mysql.exe','--no-defaults','--host=127.0.0.1','--port=13306','--user=root',h.DB,'-N','-e',"SELECT id FROM users WHERE username='"+username+"'"],check=True,capture_output=True,text=True)
user_id=int(result.stdout.strip())
requester=h.Client()
assert requester.request(data={'action':'password-reset-request','username':username,'csrf_token':requester.token()})[0]==302
mail=json.loads(pathlib.Path(os.environ['FSR_TEST_MAIL_CAPTURE']).read_text())
assert mail['email']=='reset-'+username+'@example.invalid'
code=mail['code']
assert requester.request('index.php?password_reset_link='+mail['link'])[0]==302
_,_,html=requester.request('index.php?password_reset_check=1')
assert 'data-reset-check-username value="'+username+'"' in html
assert 'data-force-open="false"' in html
h.passed('Email link pre-fills confirmation screen without authorizing reset')
attacker=h.Client()
assert attacker.request(data={'action':'password-reset-check','username':username,'csrf_token':attacker.token()})[0]==302
_,_,html=attacker.request()
assert 'id="changePasswordModal" tabindex="-1" aria-hidden="true" data-force-open="false"' in html
h.passed('Reset cannot be claimed with username alone over HTTP')
assert requester.request(data={'action':'password-reset-check','username':username,'reset_code':code,'csrf_token':requester.token()})[0]==302
_,_,html=requester.request()
assert 'id="changePasswordModal" tabindex="-1" aria-hidden="true" data-force-open="true"' in html
h.passed('Emailed code grants reset in requesting session')
password='Reset-verified-passphrase-2026'
assert requester.request(data={'action':'password-reset-complete','username':username,'password':password,'password_confirmation':password,'csrf_token':requester.token()})[0]==302
token=requester.token(); before=requester.sid()
assert requester.request(data={'action':'login','username':username,'password':password,'csrf_token':token})[0]==302
assert before!=requester.sid()
session=pathlib.Path('C:/xampp/fsr-private/test-data/sessions/sess_'+requester.sid()).read_text()
assert 'password_fingerprint' in session
h.passed('Complete reset allows login with new password')
assert attacker.request(data={'action':'password-reset-check','username':username,'reset_code':code,'csrf_token':attacker.token()})[0]==302
_,_,html=attacker.request()
assert 'id="changePasswordModal" tabindex="-1" aria-hidden="true" data-force-open="false"' in html
h.passed('Consumed reset code rejected over HTTP')

# A separate account bucket must survive fresh cookies/sessions.
limited_username = 'limit-' + secrets.token_hex(8)
for attempt in range(6):
    client = h.Client()
    status, headers, _ = client.request(data={
        'action': 'password-reset-check', 'username': limited_username,
        'reset_code': '000000', 'csrf_token': client.token(),
    })
    assert status == (302 if attempt < 5 else 429), (attempt, status)
assert headers['Retry-After'] == '1800'
h.passed('Sixth reset code check is blocked across fresh sessions')

h.sql("DELETE FROM audit_logs WHERE user_id="+str(user_id))
h.sql("DELETE FROM users WHERE id="+str(user_id))
