import s from "./home.module.css";

const ITEMS = [
  {
    year: "Year 1",
    pill: "Live Now",
    title: "MVP and retention",
    text: "Secure capture flow, AI analysis, Firestore persistence, real-time feed, onboarding, auth, and mobile polish.",
  },
  {
    year: "Year 2",
    title: "Memory graph growth",
    text: "Advanced memory graph, proactive insights, integrations, stronger personalization, export and delete flows.",
  },
  {
    year: "Year 3",
    title: "Platform readiness",
    text: "Cross-device intelligence, agent workflows, ecosystem integrations, and privacy-focused expansion.",
  },
];

export default function Roadmap() {
  return (
    <section id="roadmap" className="section">
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            Roadmap
          </span>
          <h2>Built around the real product</h2>
        </div>

        <div className={s.roadmap}>
          {ITEMS.map((item) => (
            <div key={item.year} className={s.roadItem}>
              <div className={s.roadYear}>
                <span className={s.roadYearLabel}>{item.year}</span>
                {item.pill && <span className={s.roadPill}>{item.pill}</span>}
              </div>
              <div>
                <h3>{item.title}</h3>
                <p>{item.text}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
