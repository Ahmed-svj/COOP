# Share COOP with a Cloudflare link

This gives the **real, running COOP system** a public `https://….trycloudflare.com` address. Your team can open it from any device, and logins, forms, uploads and check-ins all work and are saved. It uses a free **Cloudflare Quick Tunnel**, so you need no Cloudflare account or domain.

The link points at the computer where COOP is running, so it works **while that computer and the share window stay on**.

## 1. Have COOP running on your computer

Use one of these guides:
- **[LARAGON.md](LARAGON.md):** run `install-laragon.bat` once.
- **[VSCODE.md](VSCODE.md):** run the task "COOP: Install database + demo data" once.

MySQL must be running (in Laragon, press **Start All**).

## 2. Install cloudflared (once)

- **Windows:** the share script installs it for you with `winget`. You can also install it yourself: `winget install Cloudflare.cloudflared`.
- **macOS:** `brew install cloudflared`
- **Linux:** follow https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/downloads/

## 3. Share

- **Windows:** double-click **`share-cloudflare.bat`**, or run it from the Laragon Terminal.
- **macOS / Linux:** `./share-cloudflare.sh`
- **VS Code:** **Terminal → Run Task → "COOP: Share with a Cloudflare link"**.

The script starts COOP on port 8080 if it isn't running yet. After a few seconds it prints a box like this:

```text
|  https://quiet-river-sample-words.trycloudflare.com  |
```

Send that link to your team. **Ctrl+C** (or closing the window) stops sharing. Each new run gives a **new** link.

## Before you share

- **Change the demo passwords.** Every demo account uses the publicly known password `Coop2026!`, so anyone with the link could log in as the admin. Change at least the admin's password first (**Settings → Security**), or share the link only with people you trust.
- **Leave debug mode off.** Keep `APP_DEBUG=false` in `.env`, so visitors never see error details.
- **Expect some limits.** Quick Tunnels are meant for demos and testing. For a link that stays the same, make a free Cloudflare account and a *named tunnel*: https://developers.cloudflare.com/cloudflare-one/connections/connect-networks/

COOP was tested behind Cloudflare-style forwarding: secure cookies, redirects and the language switch all use the `trycloudflare.com` address correctly.
