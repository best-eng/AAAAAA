"use client";

import { useState } from "react";
import { ChevronDown } from "./Icons";
import styles from "./Faq.module.css";

const ITEMS = [
  {
    q: "How does Cydit's AI memory work?",
    a: "Cydit uses advanced large language models to understand the semantic meaning of everything you capture. It creates vector embeddings of your memories and builds a knowledge graph that connects related ideas, people, and events — making everything searchable and discoverable through natural language.",
  },
  {
    q: "Is my data private and secure?",
    a: "Yes. Your memories are encrypted before they leave your device with a zero-knowledge architecture, and we never train our models on your data. You stay in full control and can export or delete everything at any time.",
  },
  {
    q: "What languages does Cydit support?",
    a: "You can speak or type in dozens of languages. Cydit's AI understands intent and context across languages, so you can capture a thought in one language and recall it in another without losing meaning.",
  },
  {
    q: "How is Cydit different from Notion or Apple Notes?",
    a: "Traditional note apps store text you have to organize and find yourself. Cydit understands what you capture, connects it automatically into a living knowledge graph, and proactively surfaces the right memory before you even search for it.",
  },
  {
    q: "Can I export my data?",
    a: "Absolutely. You can export your full memory graph and raw captures at any time in open formats. Your thoughts belong to you — Pro and Premium plans include dedicated export tools.",
  },
  {
    q: "What happens to my memories if I cancel?",
    a: "Your data is never held hostage. If you cancel, you can export everything first, and we retain your memories on a free tier so you can come back any time. You can also permanently delete your account and all data on request.",
  },
];

export default function Faq() {
  const [open, setOpen] = useState(0);

  return (
    <div className={styles.list}>
      {ITEMS.map((item, i) => {
        const isOpen = open === i;
        return (
          <div key={i} className={`${styles.item} ${isOpen ? styles.itemOpen : ""}`}>
            <button
              type="button"
              className={styles.q}
              aria-expanded={isOpen}
              onClick={() => setOpen(isOpen ? -1 : i)}
            >
              <span>{item.q}</span>
              <ChevronDown
                width={20}
                height={20}
                className={styles.chevron}
                style={{ transform: isOpen ? "rotate(180deg)" : "none" }}
              />
            </button>
            <div className={styles.answer} style={{ maxHeight: isOpen ? "300px" : "0" }}>
              <p>{item.a}</p>
            </div>
          </div>
        );
      })}
    </div>
  );
}
