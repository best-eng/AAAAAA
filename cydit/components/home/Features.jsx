import {
  Mic,
  Brain as BrainIcon,
  ListChecks,
  Cpu,
  Target,
  CalendarClock,
  Sparkles,
  Network,
  Search,
  Users,
  Sprout,
  MessageCircle,
} from "../Icons";
import s from "./home.module.css";

const FEATURES = [
  { Icon: Mic, title: "Voice Capture", text: "Hands-free voice recording with instant AI transcription and understanding." },
  { Icon: BrainIcon, title: "AI Memory", text: "Long-term memory that never forgets. Context-aware recall of everything." },
  { Icon: ListChecks, title: "Smart Tasks", text: "AI extracts action items from your thoughts and auto-organizes them." },
  { Icon: Cpu, title: "Context Engine", text: "Deep understanding of who, what, when, and why behind every memory." },
  { Icon: Target, title: "Goal Tracking", text: "Track long-term goals with AI that monitors your progress automatically." },
  { Icon: CalendarClock, title: "Calendar AI", text: "Smart scheduling that understands your priorities and energy levels." },
  { Icon: Sparkles, title: "AI Insights", text: "Daily intelligence reports revealing patterns in your thinking and habits." },
  { Icon: Network, title: "Knowledge Graph", text: "Visual map of every idea, person, and project connected to each other." },
  { Icon: Search, title: "Cross Memory Search", text: "Semantic search across your entire life history in milliseconds." },
  { Icon: Users, title: "Relationships", text: "Track important people, conversations, and relationship history." },
  { Icon: Sprout, title: "Second Brain", text: "Complete cognitive extension. Think bigger, decide faster, forget nothing." },
  { Icon: MessageCircle, title: "AI Chat", text: "Talk to your own memories. Ask anything about your past thoughts." },
];

export default function Features() {
  return (
    <section id="features" className="section">
      <div className="glow glow--blue" style={{ width: 520, height: 520, top: 120, right: -180 }} />
      <div className="container">
        <div className="section-head">
          <span className="eyebrow">
            <span className="dot" />
            Features
          </span>
          <h2>Everything your mind needs</h2>
          <p>
            Twelve powerful capabilities working together as one seamless AI
            operating system.
          </p>
        </div>

        <div className={s.featureGrid}>
          {FEATURES.map(({ Icon, title, text }) => (
            <div key={title} className={s.feature}>
              <div className="icon-badge">
                <Icon />
              </div>
              <h3>{title}</h3>
              <p>{text}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
