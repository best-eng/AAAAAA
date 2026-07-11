import LegalPage from "@/components/LegalPage";

export const metadata = {
  title: "Cookies",
  description: "How we use cookies and local storage.",
};

export default function CookiesPage() {
  return (
    <LegalPage
      eyebrow="Cookies"
      title="Cookies"
      subtitle="How we use cookies and local storage."
    >
      <p className="updated">Last updated: January 2026</p>

      <h2>What we use</h2>
      <p>
        We use cookies and local storage for login sessions, language
        preferences, form state, and privacy-respecting analytics.
      </p>

      <h2>Categories</h2>
      <ul>
        <li>
          <strong>Essential</strong> — required for authentication and core site
          functionality.
        </li>
        <li>
          <strong>Preferences</strong> — remember your language and interface
          choices.
        </li>
        <li>
          <strong>Analytics</strong> — help us understand aggregate usage to
          improve the product.
        </li>
      </ul>

      <h2>Your choices</h2>
      <p>
        You can accept or reject non-essential cookies from the banner shown on
        your first visit, and change your browser settings at any time.
      </p>

      <p>
        This page is prepared for production deployment and can be expanded with
        full legal copy for your jurisdiction.
      </p>
    </LegalPage>
  );
}
