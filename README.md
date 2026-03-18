# Spond Browser Client

An unofficial, zero-dependency browser-based client for the [Spond](https://spond.com) API — built as a single embeddable HTML file with a lightweight PHP proxy.

**Author:** Jonathan Puu  
**License:** [GPL-3.0](LICENSE)  
**Based on:** [Olen/Spond](https://github.com/Olen/Spond) — the original unofficial Python library for the Spond API (GPL-3.0, © Olen)

> ⚠️ This project is unofficial and not affiliated with or endorsed by Spond AS. Use of the Spond API is subject to Spond's own terms of service.

---

## Features

- **Members** — view all group members, click any member to message them
- **Events** — upcoming events with attendance counts
- **Messages** — full inbox, threaded chat view, reply to chats, start new DMs
- **Posts** — group feed with images, reactions, comments, nested replies, new post composer
- **Groups** — switch between groups

All in a single HTML file. No npm, no build step, no framework.

---

## Files

| File | Purpose |
|------|---------|
| `spond-embed.html` | The main client — embed this anywhere (GoHighLevel, WordPress, etc.) |
| `spond-proxy.php` | PHP reverse proxy — upload to your web host to bypass CORS |
| `.htaccess` | Apache config — required on shared hosts to pass Authorization headers through to PHP |

---

## Quick Start

### 1. Deploy the proxy

Upload `spond-proxy.php` and `.htaccess` to the same folder on your web host (e.g. `public_html/`).

### 2. Configure the client

Open `spond-embed.html` and update the two constants near the top of the `<script>`:

```js
const API_BASE      = 'https://yourdomain.com/spond-proxy.php/core/v1/';
const CHAT_API_BASE = 'https://yourdomain.com/spond-proxy.php/chat/v1/';
```

### 3. Embed

Paste the contents of `spond-embed.html` into any HTML embed block — works great in GoHighLevel custom HTML elements, WordPress pages, or any static host.

---

## How It Works

```
Browser
  └── spond-embed.html
        ├── GET/POST → yourdomain.com/spond-proxy.php/core/v1/...
        └── GET/POST → yourdomain.com/spond-proxy.php/chat/v1/...
                              │
                        spond-proxy.php
                              │
                        api.spond.com  (Spond's API)
```

The Spond API blocks direct browser requests (CORS). The PHP proxy forwards all requests server-side and adds the correct CORS headers so the browser accepts the response.

**Auth flow:**
1. `POST /core/v1/login` → Bearer token
2. `POST /core/v1/chat` → separate chat `auth` token (required for all `/chat/v1/` endpoints)
3. All subsequent calls use the appropriate token

---

## API Endpoints Used

Discovered by inspecting the official Spond web app's network traffic:

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/core/v1/login` | POST | Authenticate |
| `/core/v1/profile` | GET | Logged-in user profile |
| `/core/v1/groups/` | GET | All groups |
| `/core/v1/sponds/` | GET | Events |
| `/core/v1/chat` | POST | Exchange Bearer token for chat auth token |
| `/chat/v1/chats` | GET | Chat list |
| `/chat/v1/chats/{id}/messages` | GET | Messages in a chat |
| `/chat/v1/messages` | POST | Send a message / start a new chat |
| `/chat/v1/chats/seen` | PUT | Mark all chats read |
| `/core/v1/posts` | GET | Group posts feed |
| `/core/v1/posts` | POST | Create a new post |
| `/core/v1/posts/{id}/comments` | POST | Add a comment |
| `/core/v1/posts/{id}/comments/{commentId}` | POST | Reply to a comment |

---

## Proxy Security

By default `ALLOWED_ORIGIN` is set to `'*'` (open). Once deployed, lock it down to your site:

```php
// spond-proxy.php — line 30
define('ALLOWED_ORIGIN', 'https://your-site.com');
```

---

## Credits

This project is a browser-based JavaScript port and extension of the API client concepts pioneered by:

- **[Olen/Spond](https://github.com/Olen/Spond)** — the original unofficial Python library, GPL-3.0
  - API endpoint discovery, authentication flow, and data model understanding all draw heavily from this work.

Additional API endpoints were discovered by inspecting the Spond web application's network traffic.

---

## License

GNU General Public License v3.0 — see [LICENSE](LICENSE).

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
