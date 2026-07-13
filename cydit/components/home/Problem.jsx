import s from "./home.module.css";

const CARDS = [
  {
    emoji: "💭",
    title: "Ideas disappear",
    text: "That brilliant insight at 2am? Gone by morning. Your best thinking evaporates before you can act on it.",
  },
  {
    emoji: "📓",
    title: "Notes become graveyards",
    text: "Thousands of notes with zero connections. You write things down and never find them again.",
  },
  {
    emoji: "🔗",
    title: "Information is fragmented",
    text: "Scattered across 12 apps, 3 notebooks, and your phone camera roll. Context lost forever.",
  },
  {
    emoji: "⏰",
    title: "Tasks get lost",
    text: "Important actions buried in long threads. You forget to follow up. Things fall through the cracks.",
  },
];

export default function Problem() {
  return (
    <section className="section">
      <div className="glow glow--purple" style={{ width: 480, height: 480, top: 40, left: -160 }} />
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            The Problem
          </span>
          <h2>
            Everyone forgets.
            <br />
            <span className="gradient-text">Every. Single. Day.</span>
          </h2>
          <p>
            The human mind wasn&apos;t designed for the information age. We need a
            better system.
          </p>
        </div>

        <div className={s.problemCards}>
          {CARDS.map((c) => (
            <div key={c.title} className={s.problemCard}>
              <div className={s.problemEmoji} aria-hidden="true">
                {c.emoji}
              </div>
              <h3>{c.title}</h3>
              <p>{c.text}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
