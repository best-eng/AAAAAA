// Lightweight inline SVG icon set (lucide-style, stroke-based).
// Keeps the site dependency-free while matching the original iconography.

const base = {
  width: 24,
  height: 24,
  viewBox: "0 0 24 24",
  fill: "none",
  stroke: "currentColor",
  strokeWidth: 1.8,
  strokeLinecap: "round",
  strokeLinejoin: "round",
};

export function Mic(p) {
  return (
    <svg {...base} {...p}>
      <rect x="9" y="2" width="6" height="12" rx="3" />
      <path d="M5 10a7 7 0 0 0 14 0" />
      <path d="M12 17v5" />
    </svg>
  );
}

export function Brain(p) {
  return (
    <svg {...base} {...p}>
      <path d="M9 3a2.5 2.5 0 0 0-2.5 2.5v0A2.5 2.5 0 0 0 5 8a2.5 2.5 0 0 0 0 5 2.5 2.5 0 0 0 2 3.5A2.5 2.5 0 0 0 12 19V4a2.5 2.5 0 0 0-3-1Z" />
      <path d="M15 3a2.5 2.5 0 0 1 2.5 2.5v0A2.5 2.5 0 0 1 19 8a2.5 2.5 0 0 1 0 5 2.5 2.5 0 0 1-2 3.5A2.5 2.5 0 0 1 12 19" />
    </svg>
  );
}

export function ListChecks(p) {
  return (
    <svg {...base} {...p}>
      <path d="m3 5 2 2 3-3" />
      <path d="m3 13 2 2 3-3" />
      <path d="M12 6h9" />
      <path d="M12 14h9" />
      <path d="M12 20h9" />
    </svg>
  );
}

export function Cpu(p) {
  return (
    <svg {...base} {...p}>
      <rect x="6" y="6" width="12" height="12" rx="2" />
      <path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2" />
      <rect x="9" y="9" width="6" height="6" rx="1" />
    </svg>
  );
}

export function Target(p) {
  return (
    <svg {...base} {...p}>
      <circle cx="12" cy="12" r="9" />
      <circle cx="12" cy="12" r="5" />
      <circle cx="12" cy="12" r="1.5" />
    </svg>
  );
}

export function CalendarClock(p) {
  return (
    <svg {...base} {...p}>
      <path d="M8 2v4M16 2v4" />
      <rect x="3" y="5" width="18" height="16" rx="2" />
      <path d="M3 10h18" />
      <path d="M12 14v2.5l1.5 1" />
    </svg>
  );
}

export function Sparkles(p) {
  return (
    <svg {...base} {...p}>
      <path d="M12 3v4M12 17v4M3 12h4M17 12h4" />
      <path d="M12 8l1.4 2.6L16 12l-2.6 1.4L12 16l-1.4-2.6L8 12l2.6-1.4z" />
    </svg>
  );
}

export function Network(p) {
  return (
    <svg {...base} {...p}>
      <circle cx="12" cy="5" r="2.2" />
      <circle cx="5" cy="18" r="2.2" />
      <circle cx="19" cy="18" r="2.2" />
      <path d="M12 7.2v3.3M11 12.5 6.5 16M13 12.5 17.5 16" />
    </svg>
  );
}

export function Search(p) {
  return (
    <svg {...base} {...p}>
      <circle cx="11" cy="11" r="7" />
      <path d="m21 21-4.3-4.3" />
    </svg>
  );
}

export function Users(p) {
  return (
    <svg {...base} {...p}>
      <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
      <circle cx="9" cy="7" r="4" />
      <path d="M22 21v-2a4 4 0 0 0-3-3.85" />
      <path d="M16 3.13A4 4 0 0 1 16 11" />
    </svg>
  );
}

export function Sprout(p) {
  return (
    <svg {...base} {...p}>
      <path d="M7 20h10" />
      <path d="M12 20V9" />
      <path d="M12 9C12 6 9.5 4 6 4c0 3.5 2.5 5 6 5Z" />
      <path d="M12 11c0-2.5 2-4.5 5-4.5 0 3-2 4.5-5 4.5Z" />
    </svg>
  );
}

export function MessageCircle(p) {
  return (
    <svg {...base} {...p}>
      <path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.5L3 21l2-5.6A8.5 8.5 0 1 1 21 11.5Z" />
    </svg>
  );
}

export function Lock(p) {
  return (
    <svg {...base} {...p}>
      <rect x="4" y="10" width="16" height="11" rx="2" />
      <path d="M8 10V7a4 4 0 0 1 8 0v3" />
      <path d="M12 15v2" />
    </svg>
  );
}

export function Shield(p) {
  return (
    <svg {...base} {...p}>
      <path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6z" />
      <path d="m9 12 2 2 4-4" />
    </svg>
  );
}

export function Cloud(p) {
  return (
    <svg {...base} {...p}>
      <path d="M17.5 19a4.5 4.5 0 0 0 .5-9 6 6 0 0 0-11.6-1.5A4 4 0 0 0 6.5 19z" />
    </svg>
  );
}

export function Server(p) {
  return (
    <svg {...base} {...p}>
      <rect x="3" y="4" width="18" height="7" rx="2" />
      <rect x="3" y="13" width="18" height="7" rx="2" />
      <path d="M7 7.5h.01M7 16.5h.01" />
    </svg>
  );
}

export function ArrowRight(p) {
  return (
    <svg {...base} {...p}>
      <path d="M5 12h14M13 6l6 6-6 6" />
    </svg>
  );
}

export function Play(p) {
  return (
    <svg {...base} {...p} fill="currentColor" stroke="none">
      <path d="M7 4.5v15l13-7.5z" />
    </svg>
  );
}

export function Check(p) {
  return (
    <svg {...base} {...p}>
      <path d="M20 6 9 17l-5-5" />
    </svg>
  );
}

export function ChevronDown(p) {
  return (
    <svg {...base} {...p}>
      <path d="m6 9 6 6 6-6" />
    </svg>
  );
}

export function Globe(p) {
  return (
    <svg {...base} {...p}>
      <circle cx="12" cy="12" r="9" />
      <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" />
    </svg>
  );
}

export function Menu(p) {
  return (
    <svg {...base} {...p}>
      <path d="M4 6h16M4 12h16M4 18h16" />
    </svg>
  );
}

export function X(p) {
  return (
    <svg {...base} {...p}>
      <path d="M18 6 6 18M6 6l12 12" />
    </svg>
  );
}

export function Logo(p) {
  return (
    <svg viewBox="0 0 64 64" width="34" height="34" {...p}>
      <defs>
        <linearGradient id="cyditLogoG" x1="0" x2="1" y1="0" y2="1">
          <stop offset="0%" stopColor="#4F7CFF" />
          <stop offset="100%" stopColor="#7B61FF" />
        </linearGradient>
      </defs>
      <rect width="64" height="64" rx="16" fill="#05060A" />
      <circle cx="32" cy="32" r="18" fill="url(#cyditLogoG)" />
      <path d="M22 32h20" stroke="#fff" strokeWidth="5" strokeLinecap="round" />
      <path d="M32 22v20" stroke="#fff" strokeWidth="5" strokeLinecap="round" />
    </svg>
  );
}
