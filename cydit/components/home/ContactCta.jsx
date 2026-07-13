import Link from "next/link";
import Form from "../Form";
import s from "./home.module.css";

export default function ContactCta() {
  return (
    <>
      <section id="contact" className="section">
        <div className="container">
          <div className={s.contactGrid}>
            <div className={s.contactCopy}>
              <span className="eyebrow">
                <span className="dot" />
                Contact
              </span>
              <h2>Your memory deserves intelligence</h2>
              <p>
                Join the future of thinking. Capture everything, forget nothing,
                understand yourself.
              </p>
            </div>
            <div className={s.contactCard}>
              <Form
                fields={[
                  { name: "name", label: "Name", placeholder: "Your name" },
                  { name: "email", label: "Email", type: "email", placeholder: "you@example.com" },
                  { name: "message", label: "Message", type: "textarea", placeholder: "How can we help?" },
                ]}
                submitLabel="Send message"
                successMessage="Thanks for reaching out — we'll get back to you soon."
              />
            </div>
          </div>
        </div>
      </section>

      <section className="section section--tight">
        <div className="container">
          <div className={s.ctaBanner}>
            <h2>Your memory deserves intelligence</h2>
            <p>
              Join the future of thinking. Capture everything, forget nothing,
              understand yourself.
            </p>
            <Link href="/early-access" className={s.ctaBtn}>
              Start for Free
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
