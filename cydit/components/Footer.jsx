import Link from "next/link";
import { Logo } from "./Icons";
import styles from "./Footer.module.css";

const COLUMNS = [
  {
    title: "Product",
    links: [
      { label: "Features", href: "/#features" },
      { label: "Pricing", href: "/#pricing" },
      { label: "Roadmap", href: "/#roadmap" },
      { label: "Early Access", href: "/early-access" },
    ],
  },
  {
    title: "Company",
    links: [
      { label: "Contact", href: "/contact" },
      { label: "Sign in", href: "/login" },
      { label: "Forgot password", href: "/forgot-password" },
    ],
  },
  {
    title: "Legal",
    links: [
      { label: "Privacy Policy", href: "/privacy" },
      { label: "Terms of Service", href: "/terms" },
      { label: "Cookies", href: "/cookies" },
    ],
  },
];

export default function Footer() {
  return (
    <footer className={styles.footer}>
      <div className="container">
        <div className={styles.top}>
          <div className={styles.brandCol}>
            <Link href="/" className={styles.brand}>
              <Logo />
              <span>Cydit</span>
            </Link>
            <p className={styles.tagline}>
              AI Operating System for Your Mind. Capture thoughts, build memory,
              understand yourself.
            </p>
          </div>

          <div className={styles.cols}>
            {COLUMNS.map((col) => (
              <div key={col.title} className={styles.col}>
                <h4>{col.title}</h4>
                <ul>
                  {col.links.map((l) => (
                    <li key={l.label}>
                      <Link href={l.href}>{l.label}</Link>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>
        </div>

        <div className={styles.bottom}>
          <p>© 2024 Cydit, Inc. All rights reserved.</p>
          <Link href="/admin-login" className={styles.admin}>
            Admin
          </Link>
        </div>
      </div>
    </footer>
  );
}
