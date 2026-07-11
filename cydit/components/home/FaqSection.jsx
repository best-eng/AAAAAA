import Faq from "../Faq";

export default function FaqSection() {
  return (
    <section id="faq" className="section">
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            FAQ
          </span>
          <h2>Common questions</h2>
        </div>
        <Faq />
      </div>
    </section>
  );
}
