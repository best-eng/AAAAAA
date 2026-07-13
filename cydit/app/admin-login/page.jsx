import AuthCard from "@/components/AuthCard";
import Form from "@/components/Form";

export const metadata = {
  title: "Admin Login",
  description: "Cydit admin access.",
  robots: { index: false, follow: false },
};

export default function AdminLoginPage() {
  return (
    <AuthCard
      eyebrow="Admin Login"
      title="Admin Login"
      subtitle="Hidden admin access. Local development credentials: admin / admin123."
    >
      <Form
        fields={[
          { name: "username", label: "Username", placeholder: "admin" },
          { name: "password", label: "Password", type: "password", placeholder: "••••••••" },
        ]}
        submitLabel="Sign in"
        successMessage="Admin session started (local demo)."
        links={[{ label: "Forgot password?", href: "/forgot-password" }]}
      />
    </AuthCard>
  );
}
