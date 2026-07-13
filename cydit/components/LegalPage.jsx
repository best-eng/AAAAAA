import styles from "./LegalPage.module.css";

export default function LegalPage({ eyebrow, title, subtitle, children }) {
  return (
    <section className={styles.wrap}>
      <div className="glow glow--purple" style={{ width: 500, height: 500, top: -140, right: -120 }} />
      <div className="container">
        <div className={styles.head}>
          <span className="eyebrow">
            <span className="dot" />
            {eyebrow}
          </span>
          <h1 className={styles.title}>{title}</h1>
          {subtitle && <p className={styles.subtitle}>{subtitle}</p>}
        </div>
        <div className={styles.content}>{children}</div>
      </div>
    </section>
  );
}
