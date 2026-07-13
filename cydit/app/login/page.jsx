import AuthCard from "@/components/AuthCard";
import Form from "@/components/Form";

export const metadata = {
  title: "Sign in",
  description: "Sign in to your Cydit account.",
};

export default function LoginPage() {
  return (
    <AuthCard
      eyebrow="Login"
      title="Login"
      subtitle="User login prepared for Firebase Authentication."
    >
      <Form
        fields={[
          { name: "email", label: "Email", type: "email", placeholder: "you@example.com" },
          { name: "password", label: "Password", type: "password", placeholder: "••••••••" },
        ]}
        submitLabel="Sign in"
        successMessage="You're signed in. Welcome back to Cydit."
        links={[{ label: "Forgot password?", href: "/forgot-password" }]}
      />
    </AuthCard>
  );
}
