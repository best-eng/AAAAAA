import Link from "next/link";
import LegalPage from "@/components/LegalPage";

export const metadata = {
  title: "Admin",
  description: "Cydit admin dashboard.",
  robots: { index: false, follow: false },
};

export default function AdminPage() {
  return (
    <LegalPage
      eyebrow="Admin"
      title="Admin Dashboard"
      subtitle="Protected area — prepared for authenticated admin tooling."
    >
      <h2>Overview</h2>
      <p>
        This dashboard is a placeholder prepared for production deployment. Once
        connected to your backend (Firebase, or any provider), it can surface
        early-access requests, contact messages, user analytics, and content
        management.
      </p>
      <h2>Sections</h2>
      <ul>
        <li>Early-access waitlist &amp; approvals</li>
        <li>Contact form submissions</li>
        <li>User &amp; subscription management</li>
        <li>Feature flags and roadmap status</li>
      </ul>
      <p>
        Not signed in? Head to the{" "}
        <Link href="/admin-login">admin login</Link>.
      </p>
    </LegalPage>
  );
}
