import "./globals.css";
import Header from "@/components/Header";
import Footer from "@/components/Footer";
import CookieBanner from "@/components/CookieBanner";

export const metadata = {
  metadataBase: new URL("https://cydit.app"),
  title: {
    default: "Cydit — AI Operating System for Your Mind",
    template: "%s · Cydit",
  },
  description: "Cydit is an AI operating system for your mind.",
  robots: { index: true, follow: true },
  icons: { icon: "/favicon.svg" },
  openGraph: {
    title: "Cydit — AI Operating System for Your Mind",
    description:
      "Capture thoughts, build memory, understand yourself. Let AI do the rest.",
    url: "https://cydit.app",
    siteName: "Cydit",
    images: [{ url: "/og-image.svg", width: 1200, height: 630 }],
    type: "website",
  },
  twitter: {
    card: "summary_large_image",
    title: "Cydit — AI Operating System for Your Mind",
    description:
      "Capture thoughts, build memory, understand yourself. Let AI do the rest.",
    images: ["/og-image.svg"],
  },
};

export const viewport = {
  themeColor: "#05060a",
  width: "device-width",
  initialScale: 1,
};

export default function RootLayout({ children }) {
  return (
    <html lang="en">
      <body>
        <Header />
        <main>{children}</main>
        <Footer />
        <CookieBanner />
      </body>
    </html>
  );
}
