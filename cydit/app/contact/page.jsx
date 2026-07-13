import AuthCard from "@/components/AuthCard";
import Form from "@/components/Form";

export const metadata = {
  title: "Contact",
  description: "Get in touch with the Cydit team.",
};

export default function ContactPage() {
  return (
    <AuthCard
      eyebrow="Contact"
      title="Contact"
      subtitle="Send a working local form submission prepared for SMTP, Resend, Firebase, or any backend."
    >
      <Form
        fields={[
          { name: "name", label: "Name", placeholder: "Your name" },
          { name: "email", label: "Email", type: "email", placeholder: "you@example.com" },
          { name: "message", label: "Message", type: "textarea", placeholder: "How can we help?" },
        ]}
        submitLabel="Send message"
        successMessage="Thanks for reaching out — we'll get back to you soon."
      />
    </AuthCard>
  );
}
