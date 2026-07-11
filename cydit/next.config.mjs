import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // The parent directory also has a lockfile; pin the workspace root here.
  turbopack: {
    root: __dirname,
  },
};

export default nextConfig;
