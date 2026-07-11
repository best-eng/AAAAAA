import Link from "next/link";
import { Check } from "../Icons";
import s from "./home.module.css";

const PLANS = [
  {
    name: "Free",
    desc: "Start building your second brain",
    price: "$0",
    period: "/mo",
    features: ["100 captures/month", "Basic AI memory", "7-day history", "Web app"],
    cta: "Start Free",
    featured: false,
  },
  {
    name: "Pro",
    desc: "More memory, smarter search, and the core AI workflow for builders",
    price: "$9.99",
    period: "/mo",
    features: [
      "Unlimited captures",
      "Full AI memory",
      "Knowledge graph",
      "Daily AI insights",
      "Priority support",
    ],
    cta: "Start Pro",
    featured: true,
    badge: "Most Popular",
  },
  {
    name: "Premium",
    desc: "Everything in Pro plus advanced memory depth and future-first access",
    price: "$19.99",
    period: "/mo",
    features: [
      "Everything in Pro",
      "Advanced memory graph",
      "Proactive insights",
      "Export tools",
      "Early access to new releases",
    ],
    cta: "Start Premium",
    featured: false,
  },
];

export default function Pricing() {
  return (
    <section id="pricing" className="section">
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            Pricing
          </span>
          <h2>Simple, transparent pricing</h2>
          <p>Start free. Scale as you grow.</p>
        </div>

        <div className={s.pricingGrid}>
          {PLANS.map((plan) => (
            <div
              key={plan.name}
              className={`${s.plan} ${plan.featured ? s.planFeatured : ""}`}
            >
              {plan.badge && <span className={s.planBadge}>{plan.badge}</span>}
              <h3 className={s.planName}>{plan.name}</h3>
              <p className={s.planDesc}>{plan.desc}</p>
              <div className={s.planPrice}>
                <b>{plan.price}</b>
                <span>{plan.period}</span>
              </div>
              <ul className={s.planFeatures}>
                {plan.features.map((f) => (
                  <li key={f}>
                    <Check />
                    {f}
                  </li>
                ))}
              </ul>
              <Link
                href="/early-access"
                className={`btn ${plan.featured ? "btn-primary" : "btn-ghost"} btn-block`}
              >
                {plan.cta}
              </Link>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
