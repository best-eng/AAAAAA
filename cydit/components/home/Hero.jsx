import Link from "next/link";
import { ArrowRight, Play } from "../Icons";
import NodeGraph from "../NodeGraph";
import s from "./home.module.css";

export default function Hero() {
  return (
    <section id="top" className={s.hero}>
      <div className={s.heroBg} />
      <div className="container">
        <div className={s.heroInner}>
          <span className="eyebrow">
            <span className="dot" />
            Introducing Cydit
          </span>

          <h1 className={s.heroTitle}>
            Your AI Operating
            <br />
            System <span className="gradient-text">for Your Mind</span>
          </h1>

          <p className={s.heroSub}>
            Capture thoughts. Build memory. Understand yourself.{" "}
            <span className={s.dim}>Let AI do the rest.</span>
          </p>

          <div className={s.heroBtns}>
            <Link href="/early-access" className="btn btn-primary">
              Get Started Free <ArrowRight width={18} height={18} />
            </Link>
            <Link href="/#demo" className="btn btn-ghost">
              <Play width={16} height={16} /> Watch Demo
            </Link>
          </div>

          <p className={s.heroNote}>
            No credit card required · Free forever plan · 2 min setup
          </p>
        </div>

        <div className={s.heroVisual}>
          <NodeGraph />
        </div>
      </div>
    </section>
  );
}
