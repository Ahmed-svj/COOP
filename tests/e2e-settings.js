// Dark mode default + theme button, Settings without the Arabic option, and changing email.
const { chromium } = require('playwright');
const B = process.env.BASE || 'http://127.0.0.1:8080';
let fails = 0; const ok = (c, m) => { console.log((c ? 'ok   ' : 'FAIL ') + m); if (!c) fails++; };
const theme = (pg) => pg.evaluate(() => document.documentElement.dataset.theme);
(async () => {
  const b = await chromium.launch();
  const pg = await (await b.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  const errs = []; pg.on('pageerror', (e) => errs.push(String(e)));
  pg.on('dialog', (d) => d.accept());
  await pg.goto(B + '/');
  ok(await theme(pg) === 'dark', 'public site opens in dark mode');
  await pg.click('header [data-theme-toggle]'); await pg.waitForTimeout(400);
  ok(await theme(pg) === 'light', 'theme button switches a visitor to light instantly');
  await pg.reload(); ok(await theme(pg) === 'light', 'visitor choice kept for the visit');
  await pg.click('header [data-theme-toggle]'); await pg.waitForTimeout(300);

  await pg.goto(B + '/login'); await pg.fill('#email', 'student@ecoop.test'); await pg.fill('#password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]);
  ok(await theme(pg) === 'dark', 'dashboard opens in dark mode by default');
  await pg.click('.app-topbar [data-theme-toggle]'); await pg.waitForTimeout(500);
  ok(await theme(pg) === 'light', 'top-bar theme button switches to light');
  await pg.reload(); ok(await theme(pg) === 'light', 'light choice saved to the account');
  await pg.click('.app-topbar [data-theme-toggle]'); await pg.waitForTimeout(500);
  await pg.reload(); ok(await theme(pg) === 'dark', 'and back to dark, saved');

  await pg.goto(B + '/settings');
  ok(await pg.locator('main input[name=lang_ar]').count() === 0, 'Settings no longer has the Arabic option');
  ok(await pg.locator('main input[name=dark_mode]').count() === 1, 'Settings still has the dark mode switch');
  ok(await pg.locator('.app-langbtn').count() === 1, 'language button still in the top bar');
  await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings"] button[type=submit]')]);
  ok(await pg.evaluate(() => document.documentElement.lang) === 'en' && await theme(pg) === 'dark', 'saving Settings keeps language and theme');

  const change = async (email, pass) => {
    await pg.goto(B + '/settings');
    await pg.fill('#new_email', email); await pg.fill('#email_password', pass);
    await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/settings/email"] button[type=submit]')]);
    return (await pg.locator('main').textContent());
  };
  ok((await change('student2@ecoop.test', 'wrong-pass1')).includes('Current password is incorrect'), 'wrong password blocks email change');
  ok((await change('company@ecoop.test', 'Coop2026!')).includes('already registered'), 'email already in use is refused');
  await pg.goto(B + '/settings'); await pg.fill('#new_email', 'me@gmail.com'); await pg.fill('#email_password', 'Coop2026!');
  await pg.click('form[action$="/settings/email"] button[type=submit]'); await pg.waitForTimeout(300);
  ok(pg.url().endsWith('/settings') && (await pg.locator('form[action$="/settings/email"]').textContent()).includes('college email'), 'student must keep a college email (checked on the page)');
  const srv = await pg.context().request.post(B + '/settings/email', { form: { _csrf: await pg.locator('meta[name=csrf-token]').getAttribute('content'), new_email: 'me@gmail.com', email_password: 'Coop2026!' } });
  const now = require('child_process').execSync(`mysql -uroot -N coop_db -e "SELECT COUNT(*) FROM users WHERE Email='me@gmail.com'"`).toString().trim();
  ok(now === '0', 'and refused by the server too (email unchanged)');
  ok((await change('student2@ecoop.test', 'Coop2026!')).includes('Email changed to student2@ecoop.test'), 'email changed');
  await pg.click('.co-userchip .app-userchip__toggle'); await Promise.all([pg.waitForNavigation(), pg.click('form[action$="/logout"] button')]);
  await pg.fill('#email', 'student2@ecoop.test'); await pg.fill('#password', 'Coop2026!');
  await Promise.all([pg.waitForNavigation(), pg.click('main button[type=submit]')]);
  ok(pg.url().endsWith('/student/dashboard'), 'can log in with the new email');
  await pg.goto(B + '/settings'); await change('student@ecoop.test', 'Coop2026!');  // put it back
  ok(errs.length === 0, 'no JavaScript errors ' + errs.join(' | '));
  await b.close(); process.exit(fails ? 1 : 0);
})();
