"use client";

import { useEffect, useState } from "react";
import styles from "./CookieBanner.module.css";

const KEY = "cydit-cookie-consent";

export default function CookieBanner() {
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    try {
      if (!localStorage.getItem(KEY)) setVisible(true);
    } catch {
      setVisible(true);
    }
  }, []);

  const decide = (choice) => {
    try {
      localStorage.setItem(KEY, choice);
    } catch {
      /* ignore */
    }
    setVisible(false);
  };

  if (!visible) return null;

  return (
    <div className={styles.wrap} role="dialog" aria-label="Cookie consent">
      <div className={`container ${styles.inner}`}>
        <div className={styles.text}>
          <strong>Cookies and local data</strong>
          <span>We use cookies for login, language, forms, and analytics.</span>
        </div>
        <div className={styles.actions}>
          <button
            type="button"
            className="btn btn-ghost btn-sm"
            onClick={() => decide("rejected")}
          >
            Reject
          </button>
          <button
            type="button"
            className="btn btn-primary btn-sm"
            onClick={() => decide("accepted")}
          >
            Accept
          </button>
        </div>
      </div>
    </div>
  );
}
