import s from "./home.module.css";

const LOGOS = [
  "Google",
  "OpenAI",
  "Firebase",
  "Flutter",
  "Docker",
  "GitHub",
  "Stripe",
  "Vercel",
];

export default function TrustedBy() {
  const doubled = [...LOGOS, ...LOGOS];
  return (
    <section className={s.trusted}>
      <div className="container">
        <p className={s.trustedLabel}>Trusted by builders at</p>
      </div>
      <div className={s.marquee}>
        <div className={s.marqueeTrack}>
          {doubled.map((name, i) => (
            <span key={i}>{name}</span>
          ))}
        </div>
      </div>
    </section>
  );
}
