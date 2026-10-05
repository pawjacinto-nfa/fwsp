"""Run against the isolated PHP test server; never against a deployed system."""
import http.cookiejar, pathlib, re, subprocess, urllib.error, urllib.parse, urllib.request
BASE = 'http://127.0.0.1:18089/'
DB = 'fsr_security_verify_20261002'
def sql(statement):
    subprocess.run(['C:/xampp/mysql/bin/mysql.exe', '--no-defaults', '--host=127.0.0.1', '--port=13306', '--user=root', DB, '-e', statement], check=True, capture_output=True)
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args): return None
class Client:
    def __init__(self):
        self.cookies=http.cookiejar.CookieJar()
        self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.cookies), NoRedirect())
    def request(self, path='', data=None, headers=None):
        req=urllib.request.Request(BASE+path, None if data is None else urllib.parse.urlencode(data).encode(), headers=headers or {})
        try: response=self.opener.open(req)
        except urllib.error.HTTPError as error: response=error
        return response.code, response.headers, response.read().decode(errors='replace')
    def token(self):
        code,_,html=self.request()
        assert code==200, (code,html[:80])
        return re.search(r'name="csrf_token" value="([a-f0-9]+)"',html).group(1)
    def sid(self): return next(c.value for c in self.cookies if c.name=='FSRSESSID')
    def login(self, username):
        token=self.token(); before=self.sid()
        code,_,_=self.request(data={'action':'login','username':username,'password':'Verify-only-passphrase-2026','csrf_token':token})
        assert code==302 and before!=self.sid(), 'Login must rotate session ID'
def passed(message): print('PASS:',message)
if __name__ == "__main__":
    anon=Client()
    code,_,_=anon.request(data={'action':'login'}, headers={'X-Requested-With':'fetch'})
    assert code==419; passed('Missing CSRF token rejected')
    code,_,_=anon.request('index.php?page=users')
    assert code==302; passed('Anonymous access to user administration denied')
    admin=Client(); admin.login('SECURITY_VERIFY_ADMIN')
    assert admin.request('index.php?page=users')[0]==200
    passed('Administrator login works and session ID rotates')
    user=Client(); user.login('SECURITY_VERIFY_USER')
    assert user.request('index.php?page=users')[0]==302
    passed('Non-administrator cannot access user administration')
    sql("UPDATE users SET role='Read-Only User' WHERE username='SECURITY_VERIFY_ADMIN'")
    assert admin.request('index.php?page=users')[0]==302
    passed('Role changes apply to existing sessions')
    sql("UPDATE users SET is_active=0 WHERE username='SECURITY_VERIFY_USER'")
    assert user.request('index.php?page=account')[0] in (200,302)
    session=pathlib.Path('C:/xampp/fsr-private/test-data/sessions/sess_'+user.sid()).read_text()
    assert 'password_fingerprint' not in session
    passed('Account deactivation invalidates existing authentication')
    sql("UPDATE users SET is_active=1 WHERE username='SECURITY_VERIFY_USER'")
    user.login('SECURITY_VERIFY_USER')
    sessionfile=pathlib.Path('C:/xampp/fsr-private/test-data/sessions/sess_'+user.sid())
    sessionfile.write_text(re.sub(r'last_activity\|i:\d+;', 'last_activity|i:1;', sessionfile.read_text()))
    user.request()
    assert 'password_fingerprint' not in pathlib.Path('C:/xampp/fsr-private/test-data/sessions/sess_'+user.sid()).read_text()
    passed('Idle timeout removes authentication')
    user.login('SECURITY_VERIFY_USER')
    sql("UPDATE users SET password_hash='changed-for-session-revocation-test' WHERE username='SECURITY_VERIFY_USER'")
    user.request()
    assert 'password_fingerprint' not in pathlib.Path('C:/xampp/fsr-private/test-data/sessions/sess_'+user.sid()).read_text()
    passed('Password changes invalidate other sessions')
    limited=Client(); token=limited.token()
    for attempt in range(16):
        code,_,_=limited.request(data={'action':'login','username':'SECURITY_RATE_LIMIT_TEST','password':'invalid','csrf_token':token})
    assert code==429
    passed('Repeated login attempts return HTTP 429')
