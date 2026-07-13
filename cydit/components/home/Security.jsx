import { Lock, Shield, Cloud, Server } from "../Icons";
import s from "./home.module.css";

const CARDS = [
  {
    Icon: Lock,
    title: "End-to-End Encrypted",
    text: "Your memories are encrypted before leaving your device. Zero-knowledge architecture.",
  },
  {
    Icon: Shield,
    title: "Privacy First",
    text: "We never train on your data. Your thoughts are yours, forever.",
  },
  {
    Icon: Cloud,
    title: "Cloud Sync",
    text: "Secure, real-time sync across all your devices with military-grade encryption.",
  },
  {
    Icon: Server,
    title: "Secure Infrastructure",
    text: "Built on enterprise-grade Firebase infrastructure with SOC 2 compliance.",
  },
];

export default function Security() {
  return (
    <section className="section">
      <div className="glow glow--cyan" style={{ width: 480, height: 480, top: 60, left: -150 }} />
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            Security &amp; Privacy
          </span>
          <h2>Your thoughts are sacred</h2>
          <p>We built privacy into every layer of Cydit.</p>
        </div>

        <div className={s.problemCards}>
          {CARDS.map(({ Icon, title, text }) => (
            <div key={title} className={s.problemCard}>
              <div className="icon-badge">
                <Icon />
              </div>
              <h3>{title}</h3>
              <p>{text}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
