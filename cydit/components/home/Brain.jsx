import { Check } from "../Icons";
import NodeGraph from "../NodeGraph";
import s from "./home.module.css";

const POINTS = [
  "AI automatically creates connections between ideas",
  "Understands context, not just keywords",
  "Surfaces insights you didn't know you had",
];

export default function Brain() {
  return (
    <section id="product" className="section">
      <div className="container">
        <div className={s.split}>
          <div>
            <span className="eyebrow">
              <span className="dot" />
              The Cydit Brain
            </span>
            <h3 className={s.splitTitle}>
              Your knowledge, <span className="gradient-text">alive</span>
            </h3>
            <p className={s.splitText}>
              Cydit doesn&apos;t just store memories — it understands them. Every
              voice note, every idea, every task gets woven into a living
              knowledge graph that grows smarter with you.
            </p>
            <ul className={s.checkList}>
              {POINTS.map((p) => (
                <li key={p} className={s.checkItem}>
                  <span className={s.checkIcon}>
                    <Check />
                  </span>
                  {p}
                </li>
              ))}
            </ul>
          </div>

          <div className={s.graphPanel}>
            <NodeGraph />
          </div>
        </div>
      </div>
    </section>
  );
}
