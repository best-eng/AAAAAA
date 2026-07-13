"use client";

import { useState } from "react";
import Link from "next/link";
import styles from "./Form.module.css";

export default function Form({
  fields,
  submitLabel,
  successMessage = "Thanks — your submission was received.",
  links = [],
}) {
  const [status, setStatus] = useState("idle"); // idle | loading | done
  const [values, setValues] = useState(() =>
    Object.fromEntries(fields.map((f) => [f.name, ""]))
  );

  const onChange = (name) => (e) =>
    setValues((v) => ({ ...v, [name]: e.target.value }));

  const onSubmit = (e) => {
    e.preventDefault();
    setStatus("loading");
    // Local demo submission — ready to be wired to a real backend.
    setTimeout(() => setStatus("done"), 700);
  };

  if (status === "done") {
    return (
      <div className={styles.success} role="status">
        <div className={styles.successIcon} aria-hidden="true">
          ✓
        </div>
        <p>{successMessage}</p>
        <button
          type="button"
          className="btn btn-ghost btn-sm"
          onClick={() => {
            setValues(Object.fromEntries(fields.map((f) => [f.name, ""])));
            setStatus("idle");
          }}
        >
          Send another
        </button>
      </div>
    );
  }

  return (
    <form className={styles.form} onSubmit={onSubmit}>
      {fields.map((f) => (
        <label key={f.name} className={styles.field}>
          <span className={styles.label}>{f.label}</span>
          {f.type === "textarea" ? (
            <textarea
              name={f.name}
              rows={f.rows || 5}
              placeholder={f.placeholder}
              required={f.required !== false}
              value={values[f.name]}
              onChange={onChange(f.name)}
            />
          ) : (
            <input
              name={f.name}
              type={f.type || "text"}
              placeholder={f.placeholder}
              required={f.required !== false}
              value={values[f.name]}
              onChange={onChange(f.name)}
            />
          )}
        </label>
      ))}

      <button
        type="submit"
        className="btn btn-primary btn-block"
        disabled={status === "loading"}
      >
        {status === "loading" ? "Please wait…" : submitLabel}
      </button>

      {links.length > 0 && (
        <div className={styles.links}>
          {links.map((l) => (
            <Link key={l.href} href={l.href}>
              {l.label}
            </Link>
          ))}
        </div>
      )}
    </form>
  );
}
