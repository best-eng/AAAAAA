# Cydit — Next.js rebuild

The Cydit marketing site (*AI Operating System for Your Mind*) rebuilt from the
ground up with **Next.js (App Router)**, plain **CSS**, and semantic **HTML**.
No UI framework, no CSS-in-JS runtime, and no external asset fetches — the whole
site is self-contained and dependency-light.

## Stack

- **Next.js 14** (App Router, React Server Components)
- **Plain CSS** — global design tokens in `app/globals.css` + colocated CSS Modules
- **Inline SVG icons** (`components/Icons.jsx`) — no icon library
- No Tailwind, no styled-components

## Getting started

```bash
npm install
npm run dev      # http://localhost:3000
```

Build for production:

```bash
npm run build
npm start
```

## Structure

```
app/
  layout.jsx          Root layout: header, footer, cookie banner, metadata
  globals.css         Design tokens + shared utility classes
  page.jsx            Home (composes the sections in components/home)
  login/              Sign in
  forgot-password/    Password reset
  admin-login/        Hidden admin access
  admin/              Admin dashboard (placeholder)
  early-access/       Waitlist form
  contact/            Contact form
  privacy/  terms/  cookies/   Legal pages
components/
  Header, Footer, CookieBanner, Form, AuthCard, LegalPage, Faq, NodeGraph, Icons
  home/               Landing-page sections (Hero, Features, Pricing, …)
public/
  favicon.svg  og-image.svg  robots.txt  sitemap.xml
```

## Notes

- Forms submit locally (demo) and show a success state. Each is ready to be
  wired to a real backend (SMTP, Resend, Firebase, etc.).
- The design system (colors, radius, gradients) lives entirely in CSS variables
  in `app/globals.css`, so re-theming is a single-file change.
