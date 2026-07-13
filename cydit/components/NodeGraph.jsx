import styles from "./NodeGraph.module.css";

// Static knowledge-graph visualization with pulsing nodes and drawn links.
const NODES = [
  { id: "c", x: 50, y: 50, r: 9, kind: "core" },
  { id: "a", x: 22, y: 26, r: 5 },
  { id: "b", x: 78, y: 24, r: 5 },
  { id: "d", x: 84, y: 62, r: 5 },
  { id: "e", x: 62, y: 82, r: 5 },
  { id: "f", x: 28, y: 76, r: 5 },
  { id: "g", x: 14, y: 52, r: 4 },
  { id: "h", x: 50, y: 14, r: 4 },
];

const LINKS = [
  ["c", "a"],
  ["c", "b"],
  ["c", "d"],
  ["c", "e"],
  ["c", "f"],
  ["a", "g"],
  ["a", "h"],
  ["b", "h"],
  ["d", "e"],
  ["f", "g"],
];

export default function NodeGraph({ className = "" }) {
  const byId = Object.fromEntries(NODES.map((n) => [n.id, n]));
  return (
    <div className={`${styles.wrap} ${className}`}>
      <svg viewBox="0 0 100 100" className={styles.svg} aria-hidden="true">
        <defs>
          <radialGradient id="ngGlow" cx="50%" cy="50%" r="50%">
            <stop offset="0%" stopColor="#4f7cff" stopOpacity="0.9" />
            <stop offset="100%" stopColor="#7b61ff" stopOpacity="0.9" />
          </radialGradient>
        </defs>
        <g className={styles.links}>
          {LINKS.map(([a, b], i) => (
            <line
              key={i}
              x1={byId[a].x}
              y1={byId[a].y}
              x2={byId[b].x}
              y2={byId[b].y}
              className={styles.link}
              style={{ animationDelay: `${i * 0.25}s` }}
            />
          ))}
        </g>
        <g>
          {NODES.map((n, i) => (
            <g key={n.id}>
              {n.kind === "core" && (
                <circle
                  cx={n.x}
                  cy={n.y}
                  r={n.r + 6}
                  className={styles.halo}
                />
              )}
              <circle
                cx={n.x}
                cy={n.y}
                r={n.r}
                fill={n.kind === "core" ? "url(#ngGlow)" : "#4f7cff"}
                className={n.kind === "core" ? styles.core : styles.node}
                style={{ animationDelay: `${i * 0.35}s` }}
              />
            </g>
          ))}
        </g>
      </svg>
    </div>
  );
}
