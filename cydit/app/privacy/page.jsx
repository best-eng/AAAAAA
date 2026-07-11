import LegalPage from "@/components/LegalPage";

export const metadata = {
  title: "Privacy Policy",
  description: "How Cydit stores and processes data.",
};

export default function PrivacyPage() {
  return (
    <LegalPage
      eyebrow="Privacy Policy"
      title="Privacy Policy"
      subtitle="How Cydit stores and processes data."
    >
      <p className="updated">Last updated: January 2026</p>

      <h2>Our commitment</h2>
      <p>
        Your thoughts are sacred. Cydit is built privacy-first: your memories are
        encrypted before they leave your device with a zero-knowledge
        architecture, and we never train our models on your personal data.
      </p>

      <h2>Information we process</h2>
      <ul>
        <li>Account details you provide, such as your name and email.</li>
        <li>Content you capture — voice notes, ideas, tasks, and related metadata.</li>
        <li>Basic usage analytics used to improve reliability and performance.</li>
      </ul>

      <h2>How we use data</h2>
      <p>
        We use your data solely to deliver the Cydit experience: building your
        knowledge graph, surfacing insights, syncing across your devices, and
        keeping your account secure. We do not sell your data.
      </p>

      <h2>Your rights</h2>
      <p>
        You can access, export, or permanently delete your data at any time. To
        exercise these rights, contact us and we will respond promptly.
      </p>

      <p>
        This page is prepared for production deployment and can be expanded with
        full legal copy for your jurisdiction.
      </p>
    </LegalPage>
  );
}
