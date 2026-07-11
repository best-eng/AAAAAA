import LegalPage from "@/components/LegalPage";

export const metadata = {
  title: "Terms of Service",
  description: "Rules and terms for using the site.",
};

export default function TermsPage() {
  return (
    <LegalPage
      eyebrow="Terms of Service"
      title="Terms of Service"
      subtitle="Rules and terms for using the site."
    >
      <p className="updated">Last updated: January 2026</p>

      <h2>Acceptance of terms</h2>
      <p>
        By accessing or using Cydit, you agree to these terms. If you do not
        agree, please do not use the service.
      </p>

      <h2>Using Cydit</h2>
      <ul>
        <li>You are responsible for the content you capture and store.</li>
        <li>You agree not to misuse the service or attempt to disrupt it.</li>
        <li>You must be old enough to form a binding contract in your region.</li>
      </ul>

      <h2>Subscriptions</h2>
      <p>
        Paid plans renew automatically unless cancelled. You can manage or cancel
        your subscription at any time; the free tier remains available.
      </p>

      <h2>Availability &amp; liability</h2>
      <p>
        We work hard to keep Cydit available and reliable, but the service is
        provided &ldquo;as is&rdquo; without warranties. To the extent permitted
        by law, our liability is limited.
      </p>

      <p>
        This page is prepared for production deployment and can be expanded with
        full legal copy for your jurisdiction.
      </p>
    </LegalPage>
  );
}
