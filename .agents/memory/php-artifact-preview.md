---
name: PHP artifact preview
description: How plain PHP sites run inside the workspace's web artifact wrapper.
---

Plain PHP sites can be previewed inside a web artifact by serving the artifact directory with PHP's built-in server; the managed workflow starts in that directory, and a small router can provide stable placeholder media paths during preview.

**Why:** The workspace is optimized for JavaScript web artifacts, but some requests explicitly require Apache-compatible PHP and need the preview to run the same source files.

**How to apply:** Keep the product source PHP/MySQL-first, use the artifact only as a preview shell, use paths relative to the artifact working directory, and document standard Apache deployment separately.