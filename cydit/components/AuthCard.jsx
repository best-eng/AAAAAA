import styles from "./AuthCard.module.css";

export default function AuthCard({ eyebrow, title, subtitle, children }) {
  return (
    <section className={styles.wrap}>
      <div className="glow glow--blue" style={{ width: 460, height: 460, top: -120, left: "50%", transform: "translateX(-50%)" }} />
      <div className={styles.card}>
        {eyebrow && (
          <span className="eyebrow">
            <span className="dot" />
            {eyebrow}
          </span>
        )}
        <h1 className={styles.title}>{title}</h1>
        {subtitle && <p className={styles.subtitle}>{subtitle}</p>}
        <div className={styles.body}>{children}</div>
      </div>
    </section>
  );
}
