import NodeGraph from "../NodeGraph";
import s from "./home.module.css";

export default function MemoryViz() {
  return (
    <section className="section">
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            AI Memory Visualization
          </span>
          <h2>
            Your mind, <span className="gradient-text">mapped</span>
          </h2>
        </div>

        <div className={s.vizPanel}>
          <div className={s.vizBadges}>
            <div className={s.vizBadge}>
              <span>Live Sync</span>
              <strong>
                <span className={s.live}>●</span> Ideas are connecting now
              </strong>
            </div>
            <div className={s.vizBadge}>
              <span>Signals</span>
              <strong>12 active links</strong>
            </div>
          </div>
          <NodeGraph />
        </div>
      </div>
    </section>
  );
}
