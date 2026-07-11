import AuthCard from "@/components/AuthCard";
import Form from "@/components/Form";

export const metadata = {
  title: "Early Access",
  description: "Join the private Cydit launch list.",
};

export default function EarlyAccessPage() {
  return (
    <AuthCard
      eyebrow="Early Access"
      title="Early Access"
      subtitle="Join the private Cydit launch list."
    >
      <Form
        fields={[
          { name: "name", label: "Name", placeholder: "Your name" },
          { name: "email", label: "Email", type: "email", placeholder: "you@example.com" },
        ]}
        submitLabel="Request access"
        successMessage="You're on the list. We'll be in touch as spots open up."
      />
    </AuthCard>
  );
}
