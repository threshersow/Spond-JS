# Contributing

Contributions are welcome! This project is GPL-3.0 licensed.

## Ways to Help

- **Bug reports** — open an issue with the browser console error and what you expected to happen
- **New Spond endpoints** — if you discover a new API endpoint by inspecting network traffic, open a PR or issue with the endpoint and payload shape
- **UI improvements** — the embed is a single HTML file; PRs for UX/accessibility improvements are appreciated
- **Proxy adapters** — nginx config, Cloudflare Worker version, Node.js proxy, etc.

## Pull Requests

1. Fork the repo
2. Make your changes in a feature branch
3. Keep the zero-dependency constraint for `spond-embed.html` — no npm, no bundler
4. Open a PR with a clear description of what changed and why

## Discovered API Endpoints

The Spond API is undocumented. If you find new endpoints by inspecting the Spond web app's network traffic, please share them in an issue — even if you don't have time to implement the feature yourself.

## License

By contributing you agree that your contributions will be licensed under GPL-3.0.
