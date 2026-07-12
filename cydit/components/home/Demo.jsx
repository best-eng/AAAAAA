"use client";

import { useEffect, useRef, useState } from "react";
import { Mic, Sparkles, Check } from "../Icons";
import s from "./home.module.css";

const TRANSCRIPT =
  "Remind me to send the roadmap update and connect Sarah's feedback to the pricing work.";
const WORDS = TRANSCRIPT.split(" ");

const INSIGHT =
  "You've mentioned “product redesign” 14 times this week — 3× more than last week. You seem to be in a high-creativity phase.";

const TASKS = [
  "Follow up with Sarah (3 days overdue)",
  "Gym reminder — you haven't worked out in 5 days",
  "Q4 budget review in 2 days",
];

// phase: recording -> analyzing -> result -> (hold) -> loop
export default function Demo() {
  const [phase, setPhase] = useState("recording");
  const [wordCount, setWordCount] = useState(0);
  const [tasksShown, setTasksShown] = useState(0);
  const timers = useRef([]);

  useEffect(() => {
    let cancelled = false;
    const wait = (ms) =>
      new Promise((resolve) => {
        const t = setTimeout(resolve, ms);
        timers.current.push(t);
      });

    async function run() {
      while (!cancelled) {
        setPhase("recording");
        setWordCount(0);
        setTasksShown(0);
        await wait(1000);

        for (let i = 1; i <= WORDS.length; i++) {
          if (cancelled) return;
          setWordCount(i);
          await wait(170);
        }
        await wait(650);

        if (cancelled) return;
        setPhase("analyzing");
        await wait(1400);

        if (cancelled) return;
        setPhase("result");
        await wait(650);

        for (let i = 1; i <= TASKS.length; i++) {
          if (cancelled) return;
          setTasksShown(i);
          await wait(550);
        }

        await wait(4200);
      }
    }

    run();
    return () => {
      cancelled = true;
      timers.current.forEach(clearTimeout);
      timers.current = [];
    };
  }, []);

  const isRecording = phase === "recording";
  const revealed = WORDS.slice(0, wordCount).join(" ");
  const seconds = Math.min(9, Math.round(wordCount * 0.28));

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
            {/* LEFT — voice capture + live transcription */}
            <div className={s.demoCard}>
              <div className={s.demoHeadline}>
                {isRecording ? (
                  <>
                    <span className={`${s.recDot} ${s.recPulse}`}>
                      <Mic width={20} height={20} />
                    </span>
                    Recording…
                  </>
                ) : (
                  <>
                    <span className={s.doneDot}>
                      <Check width={18} height={18} />
                    </span>
                    Transcribed
                  </>
                )}
              </div>

              <p className={s.quote}>
                &ldquo;{revealed}
                {isRecording ? (
                  <span className={s.caret} aria-hidden="true" />
                ) : (
                  "”"
                )}
              </p>

              <div
                className={`${s.wave} ${isRecording ? s.waveActive : s.waveIdle}`}
                aria-hidden="true"
              >
                {Array.from({ length: 30 }).map((_, i) => (
                  <i key={i} style={{ animationDelay: `${(i % 8) * 0.08}s` }} />
                ))}
              </div>

              <div className={s.demoStatus}>
                <span className={isRecording ? s.statusLive : s.statusIdle}>
                  <span className={s.statusPip} /> {isRecording ? "LIVE" : "SAVED"}
                </span>
                <span className={s.statusMeta}>
                  🇺🇸 English · 0:0{seconds}
                </span>
              </div>
            </div>

            {/* RIGHT — AI response, wired like the app */}
            <div className={s.demoCard}>
              <div className={s.demoHeadline}>
                <span className={s.intelIcon}>
                  <Sparkles width={20} height={20} />
                </span>
                Daily Intelligence
              </div>

              {phase !== "result" ? (
                <div className={s.thinking}>
                  <div className={s.thinkingRow}>
                    <span className={s.thinkingDots} aria-hidden="true">
                      <i />
                      <i />
                      <i />
                    </span>
                    <span>
                      {phase === "analyzing"
                        ? "Analyzing your memory…"
                        : "Listening…"}
                    </span>
                  </div>
                  <div className={s.skeleton}>
                    <span style={{ width: "92%" }} />
                    <span style={{ width: "78%" }} />
                    <span style={{ width: "60%" }} />
                  </div>
                </div>
              ) : (
                <>
                  <p className={s.demoText}>{INSIGHT}</p>
                  <ul className={s.demoList}>
                    {TASKS.slice(0, tasksShown).map((t) => (
                      <li key={t} className={s.taskIn}>
                        {t}
                      </li>
                    ))}
                  </ul>
                </>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
