import Hero from "@/components/home/Hero";
import TrustedBy from "@/components/home/TrustedBy";
import Problem from "@/components/home/Problem";
import Brain from "@/components/home/Brain";
import HowItWorks from "@/components/home/HowItWorks";
import Features from "@/components/home/Features";
import Security from "@/components/home/Security";
import Demo from "@/components/home/Demo";
import WhyCydit from "@/components/home/WhyCydit";
import Roadmap from "@/components/home/Roadmap";
import Pricing from "@/components/home/Pricing";
import FaqSection from "@/components/home/FaqSection";
import ContactCta from "@/components/home/ContactCta";

export default function HomePage() {
  return (
    <>
      <Hero />
      <TrustedBy />
      <Problem />
      <Brain />
      <HowItWorks />
      <Features />
      <Security />
      <Demo />
      <WhyCydit />
      <Roadmap />
      <Pricing />
      <FaqSection />
      <ContactCta />
    </>
  );
}
