"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { Logo, ChevronDown, Menu, X } from "./Icons";
import styles from "./Header.module.css";

const NAV = [
  { label: "Home", href: "/#top" },
  { label: "Product", href: "/#product" },
  { label: "Features", href: "/#features" },
  { label: "Pricing", href: "/#pricing" },
  { label: "Roadmap", href: "/#roadmap" },
  { label: "Contact", href: "/#contact" },
];

const LANGS = [
  { flag: "🇺🇸", label: "English" },
  { flag: "🇪🇸", label: "Español" },
  { flag: "🇫🇷", label: "Français" },
  { flag: "🇩🇪", label: "Deutsch" },
  { flag: "🇯🇵", label: "日本語" },
];

export default function Header() {
  const [scrolled, setScrolled] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  const [langOpen, setLangOpen] = useState(false);
  const [lang, setLang] = useState(LANGS[0]);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 12);
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = menuOpen ? "hidden" : "";
    return () => {
      document.body.style.overflow = "";
    };
  }, [menuOpen]);

  return (
    <header className={`${styles.header} ${scrolled ? styles.scrolled : ""}`}>
      <div className={`container ${styles.inner}`}>
        <Link href="/" className={styles.brand} onClick={() => setMenuOpen(false)}>
          <Logo />
          <span>Cydit</span>
        </Link>

        <nav className={styles.nav} aria-label="Primary">
          {NAV.map((item) => (
            <Link key={item.label} href={item.href} className={styles.navLink}>
              {item.label}
            </Link>
          ))}
        </nav>

        <div className={styles.actions}>
          <div
            className={styles.langWrap}
            onMouseLeave={() => setLangOpen(false)}
          >
            <button
              type="button"
              className={styles.lang}
              onClick={() => setLangOpen((v) => !v)}
              aria-haspopup="listbox"
              aria-expanded={langOpen}
            >
              <span aria-hidden="true">{lang.flag}</span>
              <span className={styles.langLabel}>{lang.label}</span>
              <ChevronDown width={16} height={16} />
            </button>
            {langOpen && (
              <ul className={styles.langMenu} role="listbox">
                {LANGS.map((l) => (
                  <li key={l.label}>
                    <button
                      type="button"
                      role="option"
                      aria-selected={l.label === lang.label}
                      onClick={() => {
                        setLang(l);
                        setLangOpen(false);
                      }}
                    >
                      <span aria-hidden="true">{l.flag}</span>
                      {l.label}
                    </button>
                  </li>
                ))}
              </ul>
            )}
          </div>

          <Link href="/login" className={styles.signin}>
            Sign in
          </Link>
          <Link href="/early-access" className="btn btn-primary btn-sm">
            Get Early Access
          </Link>
        </div>

        <button
          type="button"
          className={styles.burger}
          aria-label={menuOpen ? "Close menu" : "Open menu"}
          onClick={() => setMenuOpen((v) => !v)}
        >
          {menuOpen ? <X width={24} height={24} /> : <Menu width={24} height={24} />}
        </button>
      </div>

      {menuOpen && (
        <div className={styles.mobile}>
          <nav className={styles.mobileNav} aria-label="Mobile">
            {NAV.map((item) => (
              <Link
                key={item.label}
                href={item.href}
                className={styles.mobileLink}
                onClick={() => setMenuOpen(false)}
              >
                {item.label}
              </Link>
            ))}
          </nav>
          <div className={styles.mobileActions}>
            <Link
              href="/login"
              className="btn btn-ghost btn-block"
              onClick={() => setMenuOpen(false)}
            >
              Sign in
            </Link>
            <Link
              href="/early-access"
              className="btn btn-primary btn-block"
              onClick={() => setMenuOpen(false)}
            >
              Get Early Access
            </Link>
          </div>
        </div>
      )}
    </header>
  );
}
