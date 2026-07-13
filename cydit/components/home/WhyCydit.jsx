import { Check } from "../Icons";
import s from "./home.module.css";

const ITEMS = [
  "Voice-first input",
  "Long-term AI Memory",
  "Context understanding",
  "Knowledge Graph",
  "Auto organization",
  "Smart suggestions",
  "Relationship detection",
  "AI insights",
];

export default function WhyCydit() {
  return (
    <section className="section">
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            Why Cydit
          </span>
          <h2>Nothing else comes close</h2>
        </div>

        <div className={s.whyPanel}>
          <div className={s.whyGrid}>
            {ITEMS.map((item) => (
              <div key={item} className={s.whyItem}>
                <span className={s.whyCheck}>
                  <Check />
                </span>
                {item}
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
