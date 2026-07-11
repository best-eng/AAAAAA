import AuthCard from "@/components/AuthCard";
import Form from "@/components/Form";

export const metadata = {
  title: "Forgot Password",
  description: "Reset your Cydit password.",
};

export default function ForgotPasswordPage() {
  return (
    <AuthCard
      eyebrow="Forgot Password"
      title="Forgot Password"
      subtitle="Reset flow prepared for Firebase Authentication."
    >
      <Form
        fields={[
          { name: "email", label: "Email", type: "email", placeholder: "you@example.com" },
        ]}
        submitLabel="Send reset link"
        successMessage="If that email exists, a reset link is on its way."
        links={[{ label: "Back to sign in", href: "/login" }]}
      />
    </AuthCard>
  );
}
