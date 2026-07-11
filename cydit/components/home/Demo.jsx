import { Mic, Sparkles } from "../Icons";
import s from "./home.module.css";

const TASKS = [
  "Follow up with Sarah (3 days overdue)",
  "Gym reminder — you haven't worked out in 5 days",
  "Q4 budget review in 2 days",
];

export default function Demo() {
  return (
    <section id="demo" className="section">
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            See It In Action
          </span>
          <h2>Feel the difference</h2>
          <p>Real interface. Real intelligence. Real magic.</p>
        </div>

        <div className={s.demoWrap}>
          <div className={s.demoGrid}>
            <div className={s.demoCard}>
              <div className={s.demoHeadline}>
                <span className={`${s.recDot} ${s.recPulse}`}>
                  <Mic width={20} height={20} />
                </span>
                Recording…
              </div>
              <p className={s.quote}>
                &ldquo;Remind me to send the roadmap update and connect
                Sarah&apos;s feedback to the pricing work.&rdquo;
              </p>
              <div className={s.wave} aria-hidden="true">
                {Array.from({ length: 28 }).map((_, i) => (
                  <i key={i} style={{ animationDelay: `${(i % 7) * 0.09}s` }} />
                ))}
              </div>
            </div>

            <div className={s.demoCard}>
              <div className={s.demoHeadline}>
                <span className={s.intelIcon}>
                  <Sparkles width={20} height={20} />
                </span>
                Daily Intelligence
              </div>
              <p className={s.demoText}>
                You&apos;ve mentioned &ldquo;product redesign&rdquo; 14 times this
                week — 3× more than last week. You seem to be in a high-creativity
                phase.
              </p>
              <ul className={s.demoList}>
                {TASKS.map((t) => (
                  <li key={t}>{t}</li>
                ))}
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
