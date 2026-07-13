import s from "./home.module.css";

const STEPS = [
  {
    num: "01",
    title: "Speak naturally",
    text: "Just talk. No templates, no structure. Say what's on your mind in any language, any time.",
  },
  {
    num: "02",
    title: "AI understands context",
    text: "Cydit's AI extracts intent, entities, tasks, goals, and emotions — not just words.",
  },
  {
    num: "03",
    title: "Memory grows",
    text: "Every input gets indexed, connected, and linked to your existing knowledge automatically.",
  },
  {
    num: "04",
    title: "Receive insights automatically",
    text: "Cydit proactively surfaces patterns, reminders, and ideas you need — before you ask.",
  },
];

export default function HowItWorks() {
  return (
    <section className="section">
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            How It Works
          </span>
          <h2>
            Four steps to <span className="gradient-text">infinite memory</span>
          </h2>
        </div>

        <div className={s.steps}>
          {STEPS.map((step) => (
            <div key={step.num} className={s.step}>
              <div className={s.stepNum}>{step.num}</div>
              <h3>{step.title}</h3>
              <p>{step.text}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
