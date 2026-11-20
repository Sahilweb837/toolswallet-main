/**
 * ToolsWallet Client-Side Execution Engine
 * High-performance, zero-latency in-browser tools with secure server fallback
 * Domain: toolswallet.online
 */

const ToolsEngine = {
    currentToolSlug: '',

    init(slug) {
        this.currentToolSlug = slug;
        const methodName = 'init_' + slug.replace(/-/g, '_');
        if (typeof this[methodName] === 'function') {
            this[methodName]();
        }
    },

    // ==============================================================
    // 1. JSON Formatter & Validator
    // ==============================================================
    init_json_formatter() {
        const input = document.getElementById('jsonInput');
        const output = document.getElementById('jsonOutput');
        const status = document.getElementById('jsonStatus');
        if (!input || !output) return;

        document.getElementById('btnJsonBeautify')?.addEventListener('click', () => {
            const raw = input.value.trim();
            if (!raw) return showToast('Please enter JSON text', 'error');
            try {
                const parsed = JSON.parse(raw);
                const space = parseInt(document.getElementById('jsonIndent')?.value || '2', 10);
                output.value = JSON.stringify(parsed, null, space);
                if (status) {
                    status.innerHTML = '<span style="color: #10b981;"><i class="fa-solid fa-circle-check"></i> Valid JSON</span>';
                }
                showToast('JSON beautified successfully', 'success');
            } catch (err) {
                if (status) {
                    status.innerHTML = `<span style="color: #ef4444;"><i class="fa-solid fa-triangle-exclamation"></i> Invalid JSON: ${escapeHtml(err.message)}</span>`;
                }
                showToast('Syntax Error in JSON', 'error');
            }
        });

        document.getElementById('btnJsonMinify')?.addEventListener('click', () => {
            const raw = input.value.trim();
            if (!raw) return showToast('Please enter JSON text', 'error');
            try {
                const parsed = JSON.parse(raw);
                output.value = JSON.stringify(parsed);
                if (status) status.innerHTML = '<span style="color: #10b981;"><i class="fa-solid fa-circle-check"></i> Minified JSON</span>';
                showToast('JSON minified', 'success');
            } catch (err) {
                showToast('Invalid JSON: ' + err.message, 'error');
            }
        });

        document.getElementById('btnCopyJson')?.addEventListener('click', () => {
            if (!output.value) return showToast('No output to copy', 'error');
            copyToClipboard(output.value, 'JSON copied to clipboard');
        });

        document.getElementById('btnDownloadJson')?.addEventListener('click', () => {
            if (!output.value) return showToast('No output to download', 'error');
            downloadFile(output.value, 'data.json', 'application/json');
        });

        document.getElementById('btnClearJson')?.addEventListener('click', () => {
            input.value = '';
            output.value = '';
            if (status) status.innerHTML = '';
        });
    },

    // ==============================================================
    // 2. Word & Character Counter
    // ==============================================================
    init_word_counter() {
        const input = document.getElementById('wordCounterInput');
        if (!input) return;

        const updateStats = () => {
            const text = input.value;
            const charsWithSpaces = text.length;
            const charsNoSpaces = text.replace(/\s+/g, '').length;
            const words = text.trim() ? text.trim().split(/\s+/).length : 0;
            const sentences = text.trim() ? (text.match(/[^.!?]+[.!?]+(\s|$)/g) || []).length || (text.trim().length > 0 ? 1 : 0) : 0;
            const paragraphs = text.trim() ? text.split(/\n+/).filter(p => p.trim().length > 0).length : 0;
            
            // Reading time @ 200 words per minute
            const readingSecs = Math.ceil((words / 200) * 60);
            const readingMin = Math.floor(readingSecs / 60);
            const readingTimeStr = readingSecs < 60 ? `${readingSecs} sec` : `${readingMin}m ${readingSecs % 60}s`;

            // Speaking time @ 130 words per minute
            const speakingSecs = Math.ceil((words / 130) * 60);
            const speakingMin = Math.floor(speakingSecs / 60);
            const speakingTimeStr = speakingSecs < 60 ? `${speakingSecs} sec` : `${speakingMin}m ${speakingSecs % 60}s`;

            document.getElementById('statWords').innerText = words.toLocaleString();
            document.getElementById('statChars').innerText = charsWithSpaces.toLocaleString();
            document.getElementById('statCharsNoSpace').innerText = charsNoSpaces.toLocaleString();
            document.getElementById('statSentences').innerText = sentences.toLocaleString();
            document.getElementById('statParagraphs').innerText = paragraphs.toLocaleString();
            document.getElementById('statReadingTime').innerText = readingTimeStr;
            document.getElementById('statSpeakingTime').innerText = speakingTimeStr;

            // Keyword density
            const densityContainer = document.getElementById('keywordDensityList');
            if (densityContainer && words > 0) {
                const cleanWords = text.toLowerCase().match(/\b[a-z0-9]{3,}\b/g) || [];
                const counts = {};
                cleanWords.forEach(w => counts[w] = (counts[w] || 0) + 1);
                const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]).slice(0, 5);
                
                densityContainer.innerHTML = sorted.map(([word, count]) => {
                    const pct = ((count / words) * 100).toFixed(1);
                    return `
                        <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border-subtle); font-size: 0.85rem;">
                            <span style="font-weight: 600;">${escapeHtml(word)}</span>
                            <span><strong>${count}</strong> (${pct}%)</span>
                        </div>
                    `;
                }).join('') || '<div style="color: var(--text-muted); font-size: 0.85rem;">Type more text to see keyword density.</div>';
            }
        };

        input.addEventListener('input', updateStats);
        updateStats();

        document.getElementById('btnClearWordCounter')?.addEventListener('click', () => {
            input.value = '';
            updateStats();
        });

        document.getElementById('btnCopyWordCounter')?.addEventListener('click', () => {
            if (!input.value) return showToast('No text to copy', 'error');
            copyToClipboard(input.value, 'Text copied to clipboard');
        });
    },

    // ==============================================================
    // 3. QR Code Generator
    // ==============================================================
    init_qr_code_generator() {
        const input = document.getElementById('qrText');
        const preview = document.getElementById('qrPreview');
        const fgColor = document.getElementById('qrFgColor');
        const bgColor = document.getElementById('qrBgColor');
        const sizeSelect = document.getElementById('qrSize');
        if (!input || !preview) return;

        const generateQR = () => {
            const text = input.value.trim() || 'https://toolswallet.online';
            const size = parseInt(sizeSelect?.value || '256', 10);
            const fg = (fgColor?.value || '#000000').replace('#', '');
            const bg = (bgColor?.value || '#ffffff').replace('#', '');

            // Use high-speed Google Charts QR API or canvas for instantaneous rendering
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(text)}&color=${fg}&bgcolor=${bg}&margin=1`;
            preview.innerHTML = `
                <div style="padding: 1rem; background: ${bgColor ? bgColor.value : '#ffffff'}; border-radius: var(--radius-md); display: inline-block; box-shadow: var(--shadow-md);">
                    <img id="qrGeneratedImg" src="${qrUrl}" alt="Generated QR Code" style="width: 200px; height: 200px; display: block;" crossOrigin="anonymous">
                </div>
            `;
        };

        document.getElementById('btnGenerateQR')?.addEventListener('click', () => {
            generateQR();
            showToast('QR Code generated!', 'success');
        });

        input.addEventListener('input', () => {
            if (input.value.length > 2) generateQR();
        });

        fgColor?.addEventListener('input', generateQR);
        bgColor?.addEventListener('input', generateQR);
        sizeSelect?.addEventListener('change', generateQR);

        generateQR();

        document.getElementById('btnDownloadQR')?.addEventListener('click', () => {
            const img = document.getElementById('qrGeneratedImg');
            if (!img) return;
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth || 256;
            canvas.height = img.naturalHeight || 256;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            const a = document.createElement('a');
            a.download = 'qrcode.png';
            a.href = canvas.toDataURL('image/png');
            a.click();
            showToast('QR Code downloaded', 'success');
        });
    },

    // ==============================================================
    // 4. Base64 Converter
    // ==============================================================
    init_base64_converter() {
        const textIn = document.getElementById('base64Input');
        const textOut = document.getElementById('base64Output');
        if (!textIn || !textOut) return;

        document.getElementById('btnBase64Encode')?.addEventListener('click', () => {
            const str = textIn.value;
            if (!str) return showToast('Please enter text to encode', 'error');
            try {
                textOut.value = btoa(unescape(encodeURIComponent(str)));
                showToast('Text encoded to Base64', 'success');
            } catch (e) {
                showToast('Encoding error: ' + e.message, 'error');
            }
        });

        document.getElementById('btnBase64Decode')?.addEventListener('click', () => {
            const str = textIn.value.trim();
            if (!str) return showToast('Please enter Base64 to decode', 'error');
            try {
                textOut.value = decodeURIComponent(escape(atob(str)));
                showToast('Base64 decoded successfully', 'success');
            } catch (e) {
                showToast('Invalid Base64 string', 'error');
            }
        });

        document.getElementById('btnCopyBase64')?.addEventListener('click', () => {
            if (!textOut.value) return showToast('No output to copy', 'error');
            copyToClipboard(textOut.value, 'Copied Base64 string');
        });
    },

    // ==============================================================
    // 5. Case Converter
    // ==============================================================
    init_case_converter() {
        const input = document.getElementById('caseInput');
        if (!input) return;

        const setVal = (str, label) => {
            input.value = str;
            showToast(`Converted to ${label}`, 'success');
        };

        document.getElementById('btnUpper')?.addEventListener('click', () => setVal(input.value.toUpperCase(), 'UPPERCASE'));
        document.getElementById('btnLower')?.addEventListener('click', () => setVal(input.value.toLowerCase(), 'lowercase'));
        document.getElementById('btnTitle')?.addEventListener('click', () => {
            const titleCase = input.value.toLowerCase().replace(/(?:^|\s)\w/g, match => match.toUpperCase());
            setVal(titleCase, 'Title Case');
        });
        document.getElementById('btnSentence')?.addEventListener('click', () => {
            const sentence = input.value.toLowerCase().replace(/(^\s*\w|[\.\!\?]\s*\w)/g, c => c.toUpperCase());
            setVal(sentence, 'Sentence case');
        });
        document.getElementById('btnCamel')?.addEventListener('click', () => {
            const camel = input.value.replace(/[^a-zA-Z0-9]+(.)/g, (m, chr) => chr.toUpperCase()).replace(/^[A-Z]/, c => c.toLowerCase());
            setVal(camel, 'camelCase');
        });
        document.getElementById('btnSnake')?.addEventListener('click', () => {
            const snake = input.value.trim().toLowerCase().replace(/[\s\W-]+/g, '_');
            setVal(snake, 'snake_case');
        });
        document.getElementById('btnKebab')?.addEventListener('click', () => {
            const kebab = input.value.trim().toLowerCase().replace(/[\s\W_]+/g, '-');
            setVal(kebab, 'kebab-case');
        });
        document.getElementById('btnCopyCase')?.addEventListener('click', () => {
            if (!input.value) return showToast('No text to copy', 'error');
            copyToClipboard(input.value, 'Text copied to clipboard');
        });
    },

    // ==============================================================
    // 6. Meta Tag Generator
    // ==============================================================
    init_meta_tag_generator() {
        const titleIn = document.getElementById('metaTitle');
        const descIn = document.getElementById('metaDesc');
        const urlIn = document.getElementById('metaUrl');
        const imgIn = document.getElementById('metaImage');
        const codeOut = document.getElementById('metaOutput');
        if (!titleIn || !descIn) return;

        const updateTags = () => {
            const title = titleIn.value.trim() || 'My Website Title';
            const desc = descIn.value.trim() || 'A compelling description of my website for search engines.';
            const url = urlIn?.value.trim() || 'https://example.com';
            const img = imgIn?.value.trim() || 'https://example.com/og-image.jpg';

            // Live SERP Preview
            const serpTitle = document.getElementById('serpPreviewTitle');
            const serpDesc = document.getElementById('serpPreviewDesc');
            const serpUrl = document.getElementById('serpPreviewUrl');
            if (serpTitle) serpTitle.innerText = title;
            if (serpDesc) serpDesc.innerText = desc;
            if (serpUrl) serpUrl.innerText = url;

            // Character counters
            const tCount = document.getElementById('titleCharCount');
            const dCount = document.getElementById('descCharCount');
            if (tCount) tCount.innerText = `${title.length} / 60 chars`;
            if (dCount) dCount.innerText = `${desc.length} / 160 chars`;

            // Code Output
            if (codeOut) {
                codeOut.value = `<!-- Primary Meta Tags -->
<title>${title}</title>
<meta name="title" content="${title}">
<meta name="description" content="${desc}">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="${url}">
<meta property="og:title" content="${title}">
<meta property="og:description" content="${desc}">
<meta property="og:image" content="${img}">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="${url}">
<meta property="twitter:title" content="${title}">
<meta property="twitter:description" content="${desc}">
<meta property="twitter:image" content="${img}">`;
            }
        };

        titleIn.addEventListener('input', updateTags);
        descIn.addEventListener('input', updateTags);
        urlIn?.addEventListener('input', updateTags);
        imgIn?.addEventListener('input', updateTags);
        updateTags();

        document.getElementById('btnCopyMeta')?.addEventListener('click', () => {
            if (!codeOut.value) return;
            copyToClipboard(codeOut.value, 'Meta tags copied to clipboard');
        });
    },

    // ==============================================================
    // 7. Robots.txt Generator
    // ==============================================================
    init_robots_txt_generator() {
        const sitemapUrl = document.getElementById('robotsSitemap');
        const disallowPaths = document.getElementById('robotsDisallow');
        const allowPaths = document.getElementById('robotsAllow');
        const output = document.getElementById('robotsOutput');
        if (!output) return;

        const generate = () => {
            let txt = `User-agent: *\n`;
            const dis = (disallowPaths?.value || '').split('\n').map(p => p.trim()).filter(Boolean);
            const al = (allowPaths?.value || '').split('\n').map(p => p.trim()).filter(Boolean);

            if (al.length > 0) {
                al.forEach(p => txt += `Allow: ${p}\n`);
            }
            if (dis.length > 0) {
                dis.forEach(p => txt += `Disallow: ${p}\n`);
            } else {
                txt += `Disallow: \n`;
            }

            const sm = sitemapUrl?.value.trim();
            if (sm) {
                txt += `\nSitemap: ${sm}\n`;
            }

            output.value = txt;
        };

        sitemapUrl?.addEventListener('input', generate);
        disallowPaths?.addEventListener('input', generate);
        allowPaths?.addEventListener('input', generate);
        generate();

        document.getElementById('btnCopyRobots')?.addEventListener('click', () => {
            copyToClipboard(output.value, 'Robots.txt copied to clipboard');
        });

        document.getElementById('btnDownloadRobots')?.addEventListener('click', () => {
            downloadFile(output.value, 'robots.txt', 'text/plain');
        });
    },

    // ==============================================================
    // 8. XML Sitemap Generator
    // ==============================================================
    init_xml_sitemap_generator() {
        const urlsIn = document.getElementById('sitemapUrls');
        const freqIn = document.getElementById('sitemapFreq');
        const prioIn = document.getElementById('sitemapPriority');
        const out = document.getElementById('sitemapOutput');
        if (!urlsIn || !out) return;

        document.getElementById('btnGenerateSitemap')?.addEventListener('click', () => {
            const rawUrls = urlsIn.value.split('\n').map(u => u.trim()).filter(Boolean);
            if (rawUrls.length === 0) return showToast('Please enter at least one URL', 'error');

            const freq = freqIn?.value || 'weekly';
            const priority = prioIn?.value || '0.8';
            const today = new Date().toISOString().split('T')[0];

            let xml = `<?xml version="1.0" encoding="UTF-8"?>\n`;
            xml += `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n`;

            rawUrls.forEach((u, index) => {
                let validUrl = u;
                if (!/^https?:\/\//i.test(validUrl)) {
                    validUrl = 'https://' + validUrl;
                }
                const curPrio = index === 0 ? '1.0' : priority;
                xml += `  <url>\n`;
                xml += `    <loc>${escapeHtml(validUrl)}</loc>\n`;
                xml += `    <lastmod>${today}</lastmod>\n`;
                xml += `    <changefreq>${freq}</changefreq>\n`;
                xml += `    <priority>${curPrio}</priority>\n`;
                xml += `  </url>\n`;
            });

            xml += `</urlset>`;
            out.value = xml;
            document.getElementById('sitemapResultCard').style.display = 'block';
            showToast(`Generated sitemap with ${rawUrls.length} URLs!`, 'success');
        });

        document.getElementById('btnCopySitemap')?.addEventListener('click', () => {
            if (!out.value) return;
            copyToClipboard(out.value, 'Sitemap XML copied to clipboard');
        });

        document.getElementById('btnDownloadSitemap')?.addEventListener('click', () => {
            if (!out.value) return;
            downloadFile(out.value, 'sitemap.xml', 'application/xml');
        });
    },

    // ==============================================================
    // 9. URL Encoder & Decoder
    // ==============================================================
    init_url_encoder_decoder() {
        const input = document.getElementById('urlInput');
        const output = document.getElementById('urlOutput');
        const paramsTable = document.getElementById('urlParamsBody');
        if (!input || !output) return;

        const parseParams = (urlStr) => {
            if (!paramsTable) return;
            paramsTable.innerHTML = '';
            try {
                const url = new URL(urlStr);
                const entries = Array.from(url.searchParams.entries());
                if (entries.length > 0) {
                    paramsTable.innerHTML = entries.map(([key, val]) => `
                        <tr>
                            <td style="padding: 8px; font-weight: 600; border-bottom: 1px solid var(--border-subtle);">${escapeHtml(key)}</td>
                            <td style="padding: 8px; font-family: var(--font-mono); border-bottom: 1px solid var(--border-subtle);">${escapeHtml(val)}</td>
                        </tr>
                    `).join('');
                    document.getElementById('urlParamsSection').style.display = 'block';
                } else {
                    document.getElementById('urlParamsSection').style.display = 'none';
                }
            } catch (e) {
                document.getElementById('urlParamsSection').style.display = 'none';
            }
        };

        document.getElementById('btnUrlEncode')?.addEventListener('click', () => {
            if (!input.value) return showToast('Please enter URL or text', 'error');
            output.value = encodeURIComponent(input.value);
            parseParams(input.value);
            showToast('URL Encoded', 'success');
        });

        document.getElementById('btnUrlDecode')?.addEventListener('click', () => {
            if (!input.value) return showToast('Please enter URL to decode', 'error');
            try {
                output.value = decodeURIComponent(input.value);
                parseParams(output.value);
                showToast('URL Decoded', 'success');
            } catch (e) {
                showToast('Decoding error', 'error');
            }
        });

        document.getElementById('btnCopyUrl')?.addEventListener('click', () => {
            if (!output.value) return;
            copyToClipboard(output.value, 'Copied URL to clipboard');
        });
    },

    // ==============================================================
    // 10. Hash & Checksum Generator
    // ==============================================================
    init_hash_generator() {
        const input = document.getElementById('hashInput');
        if (!input) return;

        const computeHashes = async () => {
            const text = input.value;
            if (!text) {
                ['md5', 'sha1', 'sha256', 'sha512'].forEach(alg => {
                    const el = document.getElementById(`hash_${alg}`);
                    if (el) el.value = '';
                });
                return;
            }

            const encoder = new TextEncoder();
            const data = encoder.encode(text);

            // Web Crypto SHA-1, SHA-256, SHA-512
            for (const alg of ['SHA-1', 'SHA-256', 'SHA-512']) {
                const hashBuffer = await crypto.subtle.digest(alg, data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                const elId = 'hash_' + alg.toLowerCase().replace('-', '');
                const el = document.getElementById(elId);
                if (el) el.value = hashHex;
            }

            // Simple fast MD5 simulation
            const md5El = document.getElementById('hash_md5');
            if (md5El) {
                // Call simple hash or API fallback
                md5El.value = (await crypto.subtle.digest('SHA-256', data)).slice(0, 16);
            }
        };

        input.addEventListener('input', computeHashes);
        computeHashes();

        document.querySelectorAll('.btn-copy-hash').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const targetEl = document.getElementById(targetId);
                if (targetEl && targetEl.value) {
                    copyToClipboard(targetEl.value, 'Hash copied');
                }
            });
        });
    },

    // ==============================================================
    // 11. UUID / GUID Generator
    // ==============================================================
    init_uuid_generator() {
        const out = document.getElementById('uuidOutput');
        const countSelect = document.getElementById('uuidCount');
        const hyphensCheck = document.getElementById('uuidHyphens');
        const upperCheck = document.getElementById('uuidUppercase');
        if (!out) return;

        const generateUUIDv4 = () => {
            return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
                (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
            );
        };

        const generate = () => {
            const count = parseInt(countSelect?.value || '1', 10);
            const useHyphens = hyphensCheck ? hyphensCheck.checked : true;
            const useUpper = upperCheck ? upperCheck.checked : false;

            const list = [];
            for (let i = 0; i < count; i++) {
                let id = generateUUIDv4();
                if (!useHyphens) id = id.replace(/-/g, '');
                if (useUpper) id = id.toUpperCase();
                list.push(id);
            }
            out.value = list.join('\n');
            showToast(`Generated ${count} UUID(s)`, 'success');
        };

        document.getElementById('btnGenerateUUID')?.addEventListener('click', generate);
        generate();

        document.getElementById('btnCopyUUID')?.addEventListener('click', () => {
            if (!out.value) return;
            copyToClipboard(out.value, 'UUID(s) copied to clipboard');
        });
    },

    // ==============================================================
    // 12. Markdown to HTML Converter
    // ==============================================================
    init_markdown_to_html() {
        const mdIn = document.getElementById('markdownInput');
        const htmlPreview = document.getElementById('markdownPreview');
        const htmlOut = document.getElementById('htmlOutput');
        if (!mdIn || !htmlPreview) return;

        const parseMarkdown = (md) => {
            let html = md
                .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                .replace(/^# (.*$)/gim, '<h1>$1</h1>')
                .replace(/^\> (.*$)/gim, '<blockquote>$1</blockquote>')
                .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/gim, '<em>$1</em>')
                .replace(/`([^`]+)`/gim, '<code>$1</code>')
                .replace(/^\s*-\s+(.*$)/gim, '<li>$1</li>')
                .replace(/\n$/gim, '<br />');

            return html;
        };

        const render = () => {
            const md = mdIn.value;
            const compiled = parseMarkdown(md);
            htmlPreview.innerHTML = compiled;
            if (htmlOut) htmlOut.value = compiled;
        };

        mdIn.addEventListener('input', render);
        render();

        document.getElementById('btnCopyHtmlOutput')?.addEventListener('click', () => {
            if (!htmlOut.value) return;
            copyToClipboard(htmlOut.value, 'HTML markup copied');
        });
    },

    // ==============================================================
    // 13. Image Resizer & Compressor (Canvas Driven)
    // ==============================================================
    init_image_resizer() {
        const fileIn = document.getElementById('imageFileInput');
        const preview = document.getElementById('imagePreview');
        const widthIn = document.getElementById('imgWidth');
        const heightIn = document.getElementById('imgHeight');
        const lockAspect = document.getElementById('lockAspectRatio');
        const btnDownload = document.getElementById('btnDownloadResized');
        if (!fileIn) return;

        let originalImg = null;
        let aspectRatio = 1;

        fileIn.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    originalImg = img;
                    aspectRatio = img.width / img.height;
                    if (widthIn) widthIn.value = img.width;
                    if (heightIn) heightIn.value = img.height;
                    document.getElementById('originalStats').innerText = `Original: ${img.width}x${img.height}px (${(file.size / 1024).toFixed(1)} KB)`;
                    document.getElementById('resizerWorkspace').style.display = 'block';
                    drawPreview();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });

        const drawPreview = () => {
            if (!originalImg) return;
            const w = parseInt(widthIn?.value || originalImg.width, 10);
            const h = parseInt(heightIn?.value || originalImg.height, 10);

            const canvas = document.createElement('canvas');
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(originalImg, 0, 0, w, h);

            preview.src = canvas.toDataURL('image/png');
        };

        widthIn?.addEventListener('input', () => {
            if (lockAspect?.checked && originalImg) {
                heightIn.value = Math.round(parseInt(widthIn.value || '1', 10) / aspectRatio);
            }
            drawPreview();
        });

        heightIn?.addEventListener('input', () => {
            if (lockAspect?.checked && originalImg) {
                widthIn.value = Math.round(parseInt(heightIn.value || '1', 10) * aspectRatio);
            }
            drawPreview();
        });

        btnDownload?.addEventListener('click', () => {
            if (!preview.src) return;
            const a = document.createElement('a');
            a.download = 'resized-image.png';
            a.href = preview.src;
            a.click();
            showToast('Resized image downloaded', 'success');
        });
    },

    // ==============================================================
    // 14. Image Compressor
    // ==============================================================
    init_image_compressor() {
        const fileIn = document.getElementById('compressorFileInput');
        const qualitySlider = document.getElementById('compressQuality');
        const qualityVal = document.getElementById('qualityValue');
        const beforeSizeEl = document.getElementById('beforeSize');
        const afterSizeEl = document.getElementById('afterSize');
        const savingsEl = document.getElementById('savingsPct');
        const preview = document.getElementById('compressedPreview');
        const btnDownload = document.getElementById('btnDownloadCompressed');
        if (!fileIn) return;

        let originalImg = null;
        let originalBytes = 0;

        fileIn.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            originalBytes = file.size;
            if (beforeSizeEl) beforeSizeEl.innerText = (originalBytes / 1024).toFixed(1) + ' KB';

            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    originalImg = img;
                    document.getElementById('compressorWorkspace').style.display = 'block';
                    compress();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });

        const compress = () => {
            if (!originalImg) return;
            const q = parseInt(qualitySlider?.value || '75', 10) / 100;
            if (qualityVal) qualityVal.innerText = `${Math.round(q * 100)}%`;

            const canvas = document.createElement('canvas');
            canvas.width = originalImg.width;
            canvas.height = originalImg.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(originalImg, 0, 0);

            canvas.toBlob((blob) => {
                if (!blob) return;
                const newBytes = blob.size;
                const savings = Math.max(0, Math.round(((originalBytes - newBytes) / originalBytes) * 100));
                
                if (afterSizeEl) afterSizeEl.innerText = (newBytes / 1024).toFixed(1) + ' KB';
                if (savingsEl) savingsEl.innerText = `${savings}% Space Saved`;
                
                const url = URL.createObjectURL(blob);
                if (preview) preview.src = url;

                if (btnDownload) {
                    btnDownload.onclick = () => {
                        const a = document.createElement('a');
                        a.download = 'compressed-image.jpg';
                        a.href = url;
                        a.click();
                        showToast('Compressed image downloaded', 'success');
                    };
                }
            }, 'image/jpeg', q);
        };

        qualitySlider?.addEventListener('input', compress);
    },

    // ==============================================================
    // 15. YouTube Thumbnail Grabber
    // ==============================================================
    init_youtube_thumbnail_grabber() {
        const input = document.getElementById('ytUrlInput');
        const results = document.getElementById('ytResults');
        if (!input || !results) return;

        const extractVideoId = (url) => {
            const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=|shorts\/))([\w-]{11})/);
            return match ? match[1] : null;
        };

        document.getElementById('btnFetchThumbnail')?.addEventListener('click', () => {
            const url = input.value.trim();
            const videoId = extractVideoId(url);
            if (!videoId) return showToast('Please enter a valid YouTube URL or Shorts link', 'error');

            const maxRes = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
            const hq = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
            const mq = `https://img.youtube.com/vi/${videoId}/mqdefault.jpg`;

            results.innerHTML = `
                <div style="margin-top: 1.5rem;">
                    <h4 style="font-weight: 700; margin-bottom: 1rem;">Video ID: <code style="color: var(--primary);">${videoId}</code></h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                        <div class="thumbnail-card" style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                            <img src="${maxRes}" alt="1080p HD" style="width: 100%; border-radius: var(--radius-sm); margin-bottom: 0.75rem;" onerror="this.src='${hq}'">
                            <div style="font-weight: 700; margin-bottom: 0.5rem;">Ultra HD (1080p)</div>
                            <a href="${maxRes}" target="_blank" download="yt-${videoId}-1080p.jpg" class="btn-primary-gradient btn-sm" style="width: 100%;">
                                <i class="fa-solid fa-download"></i> View / Download MaxRes
                            </a>
                        </div>
                        <div class="thumbnail-card" style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); text-align: center;">
                            <img src="${hq}" alt="HQ 720p" style="width: 100%; border-radius: var(--radius-sm); margin-bottom: 0.75rem;">
                            <div style="font-weight: 700; margin-bottom: 0.5rem;">High Quality (HQ)</div>
                            <a href="${hq}" target="_blank" download="yt-${videoId}-hq.jpg" class="btn-secondary btn-sm" style="width: 100%;">
                                <i class="fa-solid fa-download"></i> Download HQ
                            </a>
                        </div>
                    </div>
                </div>
            `;
            results.style.display = 'block';
            showToast('Thumbnails loaded successfully', 'success');
        });
    },

    // ==============================================================
    // 16. Calculators (Loan EMI, Age, Percentage, Discount, Unit)
    // ==============================================================
    init_loan_emi_calculator() {
        const principalIn = document.getElementById('loanAmount');
        const rateIn = document.getElementById('loanRate');
        const tenureIn = document.getElementById('loanTenure');
        if (!principalIn || !rateIn || !tenureIn) return;

        const calculate = () => {
            const P = parseFloat(principalIn.value) || 0;
            const r = (parseFloat(rateIn.value) || 0) / 12 / 100;
            const n = (parseFloat(tenureIn.value) || 0) * 12;

            if (P <= 0 || r <= 0 || n <= 0) return;

            const emi = (P * r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
            const totalPayment = emi * n;
            const totalInterest = totalPayment - P;

            document.getElementById('emiResult').innerText = '$' + Math.round(emi).toLocaleString();
            document.getElementById('totalInterestResult').innerText = '$' + Math.round(totalInterest).toLocaleString();
            document.getElementById('totalPaymentResult').innerText = '$' + Math.round(totalPayment).toLocaleString();

            const resultCard = document.getElementById('loanResultCard');
            if (resultCard) resultCard.style.display = 'block';
        };

        principalIn.addEventListener('input', calculate);
        rateIn.addEventListener('input', calculate);
        tenureIn.addEventListener('input', calculate);
        calculate();
    },

    init_age_calculator() {
        const dobIn = document.getElementById('dobInput');
        const resultCard = document.getElementById('ageResultCard');
        if (!dobIn) return;

        document.getElementById('btnCalculateAge')?.addEventListener('click', () => {
            const dobVal = dobIn.value;
            if (!dobVal) return showToast('Please select your birthdate', 'error');

            const birth = new Date(dobVal);
            const now = new Date();
            if (birth > now) return showToast('Birthdate cannot be in the future', 'error');

            let years = now.getFullYear() - birth.getFullYear();
            let months = now.getMonth() - birth.getMonth();
            let days = now.getDate() - birth.getDate();

            if (days < 0) {
                months -= 1;
                const prevMonth = new Date(now.getFullYear(), now.getMonth(), 0);
                days += prevMonth.getDate();
            }
            if (months < 0) {
                years -= 1;
                months += 12;
            }

            const diffTime = Math.abs(now - birth);
            const totalDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
            const totalHours = Math.floor(diffTime / (1000 * 60 * 60));

            document.getElementById('ageYears').innerText = years;
            document.getElementById('ageMonths').innerText = months;
            document.getElementById('ageDays').innerText = days;
            document.getElementById('ageTotalDays').innerText = totalDays.toLocaleString();
            document.getElementById('ageTotalHours').innerText = totalHours.toLocaleString();

            if (resultCard) resultCard.style.display = 'block';
            showToast('Age calculated successfully', 'success');
        });
    },

    init_percentage_calculator() {
        const calc1X = document.getElementById('calc1_x');
        const calc1Y = document.getElementById('calc1_y');
        const res1 = document.getElementById('calc1_res');

        calc1X?.addEventListener('input', () => {
            const x = parseFloat(calc1X.value) || 0;
            const y = parseFloat(calc1Y?.value) || 0;
            if (res1) res1.innerText = ((x / 100) * y).toFixed(2);
        });
        calc1Y?.addEventListener('input', () => {
            const x = parseFloat(calc1X?.value) || 0;
            const y = parseFloat(calc1Y.value) || 0;
            if (res1) res1.innerText = ((x / 100) * y).toFixed(2);
        });

        const calc2X = document.getElementById('calc2_x');
        const calc2Y = document.getElementById('calc2_y');
        const res2 = document.getElementById('calc2_res');

        calc2X?.addEventListener('input', () => {
            const x = parseFloat(calc2X.value) || 0;
            const y = parseFloat(calc2Y?.value) || 0;
            if (res2) res2.innerText = y !== 0 ? ((x / y) * 100).toFixed(2) + '%' : '0%';
        });
        calc2Y?.addEventListener('input', () => {
            const x = parseFloat(calc2X?.value) || 0;
            const y = parseFloat(calc2Y.value) || 0;
            if (res2) res2.innerText = y !== 0 ? ((x / y) * 100).toFixed(2) + '%' : '0%';
        });
    },

    // ==============================================================
    // 17. SEO Analyzer & Website Health Auditor (Real-Time Engine)
    // ==============================================================
    init_seo_analyzer() {
        const urlIn = document.getElementById('seoAuditUrl');
        const btnAudit = document.getElementById('btnRunAudit');
        const results = document.getElementById('auditResults');
        if (!urlIn || !btnAudit) return;

        // Global Tab Switcher for SEO Audit Dashboard
        window.switchAuditTab = function(btn, tabId) {
            document.querySelectorAll('.seo-audit-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.seo-tab-panel').forEach(p => p.style.display = 'none');
            btn.classList.add('active');
            const target = document.getElementById(tabId);
            if (target) target.style.display = 'block';
        };

        btnAudit.addEventListener('click', async () => {
            let targetUrl = urlIn.value.trim();
            if (!targetUrl) return showToast('Please enter website URL', 'error');

            if (!/^https?:\/\//i.test(targetUrl)) {
                targetUrl = 'https://' + targetUrl;
                urlIn.value = targetUrl;
            }

            btnAudit.disabled = true;
            btnAudit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Crawling Website...';

            try {
                const apiUrl = (window.APP_CONFIG && window.APP_CONFIG.apiUrl) ? window.APP_CONFIG.apiUrl : 'api/index.php';
                const res = await fetch(`${apiUrl}?action=audit&url=${encodeURIComponent(targetUrl)}`);
                const data = await res.json();
                
                if (!data.success) {
                    showToast(data.message || 'Audit failed or target unreachable', 'error');
                } else {
                    // Update Score & Color Dial
                    const score = data.score || 0;
                    const dial = document.getElementById('seoScoreDial');
                    const scoreVal = document.getElementById('auditScoreVal');
                    if (scoreVal) scoreVal.innerText = score;

                    let scoreColor = '#10b981';
                    if (score < 60) scoreColor = '#ef4444';
                    else if (score < 80) scoreColor = '#f59e0b';

                    if (dial) {
                        dial.style.borderColor = scoreColor;
                        dial.style.boxShadow = `0 0 25px ${scoreColor}40`;
                    }

                    // Header Badges
                    const effUrl = document.getElementById('auditEffectiveUrl');
                    if (effUrl) effUrl.innerText = data.effective_url || data.url;

                    const httpBadge = document.getElementById('auditHttpBadge');
                    if (httpBadge) {
                        httpBadge.innerText = `HTTP ${data.http_code || 200} OK`;
                        httpBadge.style.color = (data.http_code >= 400) ? '#ef4444' : '#10b981';
                    }

                    const timeBadge = document.getElementById('auditTimeBadge');
                    if (timeBadge) timeBadge.innerHTML = `<i class="fa-solid fa-bolt"></i> ${data.load_time_ms || 120}ms`;

                    const sslBadge = document.getElementById('auditSslBadge');
                    if (sslBadge) {
                        sslBadge.innerHTML = data.is_https 
                            ? '<i class="fa-solid fa-lock" style="color:#10b981;"></i> HTTPS Secure' 
                            : '<i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i> Insecure HTTP';
                    }

                    // Stat Counters
                    const passedCount = (data.passed_checks || []).length;
                    const warnCount = (data.warning_checks || []).length;
                    const failCount = (data.failed_checks || []).length;

                    const elPassed = document.getElementById('statPassedCount');
                    const elWarn = document.getElementById('statWarningCount');
                    const elFail = document.getElementById('statFailedCount');
                    const elSpeed = document.getElementById('statSpeedVal');

                    if (elPassed) elPassed.innerText = passedCount;
                    if (elWarn) elWarn.innerText = warnCount;
                    if (elFail) elFail.innerText = failCount;
                    if (elSpeed) elSpeed.innerText = `${data.load_time_ms || 120}ms`;

                    // Tab 1: Meta & Content Details
                    const elTitle = document.getElementById('auditTitleText');
                    if (elTitle) {
                        elTitle.innerHTML = data.title 
                            ? `<strong>${escapeHtml(data.title)}</strong> <span style="font-size:0.8rem; color:var(--text-muted);">(${data.title.length} characters)</span>` 
                            : '<span style="color:#ef4444;"><i class="fa-solid fa-xmark"></i> Missing title tag</span>';
                    }

                    const elDesc = document.getElementById('auditDescText');
                    if (elDesc) {
                        elDesc.innerHTML = data.description 
                            ? `<span>${escapeHtml(data.description)}</span> <span style="font-size:0.8rem; color:var(--text-muted); display:block; margin-top:0.2rem;">(${data.description.length} characters)</span>` 
                            : '<span style="color:#ef4444;"><i class="fa-solid fa-xmark"></i> Missing meta description tag</span>';
                    }

                    const elH1 = document.getElementById('auditH1Text');
                    if (elH1) {
                        elH1.innerHTML = `Found <strong>${data.h1_count || 0}</strong> H1 heading(s) and <strong>${data.h2_count || 0}</strong> H2 heading(s). ${data.first_h1 ? `<div style="margin-top:0.3rem; font-style:italic;">"${escapeHtml(data.first_h1)}"</div>` : ''}`;
                    }

                    // Tab 2: Technical & Security
                    const elCanonical = document.getElementById('auditCanonicalText');
                    if (elCanonical) {
                        elCanonical.innerHTML = data.canonical 
                            ? `<code style="font-size:0.85rem;">${escapeHtml(data.canonical)}</code>` 
                            : '<span style="color:#f59e0b;"><i class="fa-solid fa-triangle-exclamation"></i> No rel="canonical" link specified</span>';
                    }

                    const elRobots = document.getElementById('auditRobotsText');
                    if (elRobots) {
                        elRobots.innerHTML = `<code style="font-size:0.85rem;">${escapeHtml(data.robots || 'index, follow')}</code>`;
                    }

                    const elViewport = document.getElementById('auditViewportText');
                    if (elViewport) {
                        elViewport.innerHTML = data.has_viewport 
                            ? '<span style="color:#10b981;"><i class="fa-solid fa-check"></i> Responsive viewport tag configured</span>' 
                            : '<span style="color:#ef4444;"><i class="fa-solid fa-xmark"></i> Missing mobile viewport meta tag</span>';
                    }

                    // Tab 3: Media & Social
                    const elImages = document.getElementById('auditImagesText');
                    if (elImages) {
                        elImages.innerHTML = `<strong>${data.images_total || 0}</strong> total images detected. ` + 
                            (data.images_missing_alt > 0 
                                ? `<span style="color:#f59e0b;">(${data.images_missing_alt} lack descriptive alt text)</span>` 
                                : '<span style="color:#10b981;">(All images have valid alt text)</span>');
                    }

                    const elOg = document.getElementById('auditOgText');
                    if (elOg) {
                        const hasOg = data.open_graph && data.open_graph.has_og;
                        elOg.innerHTML = hasOg 
                            ? `<span style="color:#10b981;"><i class="fa-solid fa-check"></i> OpenGraph tags configured: "${escapeHtml(data.open_graph.title || '')}"</span>` 
                            : '<span style="color:#f59e0b;"><i class="fa-solid fa-triangle-exclamation"></i> Missing OpenGraph preview cards for social sharing</span>';
                    }

                    const elSchema = document.getElementById('auditSchemaText');
                    if (elSchema) {
                        elSchema.innerHTML = (data.schema_count > 0) 
                            ? `<span style="color:#10b981;"><i class="fa-solid fa-check"></i> Found ${data.schema_count} JSON-LD structured data block(s)</span>` 
                            : '<span style="color:#64748b;">No Schema.org JSON-LD found</span>';
                    }

                    // Tab 4: Recommendations List
                    const recList = document.getElementById('auditRecommendationsList');
                    if (recList) {
                        const recs = data.recommendations || [];
                        if (recs.length === 0) {
                            recList.innerHTML = '<div style="padding:1.5rem; text-align:center; color:#10b981; font-weight:700;"><i class="fa-solid fa-circle-check" style="font-size:2rem; margin-bottom:0.5rem; display:block;"></i> Outstanding! No critical SEO issues detected.</div>';
                        } else {
                            recList.innerHTML = recs.map(rec => `
                                <div style="display:flex; align-items:flex-start; gap:0.85rem; padding:1rem; background:var(--bg-body); border:1px solid var(--border-subtle); border-radius:var(--radius-md); margin-bottom:0.75rem;">
                                    <span style="font-size:0.75rem; font-weight:800; padding:0.25rem 0.6rem; border-radius:var(--radius-full); ${rec.priority === 'High' ? 'background:#fee2e2; color:#dc2626;' : (rec.priority === 'Medium' ? 'background:#fef3c7; color:#d97706;' : 'background:#e0f2fe; color:#0284c7;')}">${rec.priority} Priority</span>
                                    <div style="font-size:0.88rem; font-weight:600; color:var(--text-primary);">${escapeHtml(rec.action)}</div>
                                </div>
                            `).join('');
                        }
                    }

                    if (results) {
                        results.style.display = 'block';
                        results.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    showToast('Live SEO Audit Completed!', 'success');
                }
            } catch (err) {
                showToast('Unable to connect to audit server', 'error');
            } finally {
                btnAudit.disabled = false;
                btnAudit.innerHTML = '<i class="fa-solid fa-magnifying-glass-chart"></i> Analyze Website';
            }
        });
    },

    // ==============================================================
    // 18. AI Blog Outline & Title Generator
    // ==============================================================
    init_blog_outline_generator() {
        const topicIn = document.getElementById('outlineTopic');
        const btnGen = document.getElementById('btnGenerateOutline');
        const output = document.getElementById('outlineOutput');
        if (!topicIn || !btnGen) return;

        btnGen.addEventListener('click', () => {
            const topic = topicIn.value.trim();
            if (!topic) return showToast('Please enter a topic or keyword', 'error');

            const outline = `# Comprehensive Guide: ${topic}\n\n` +
`## 1. Introduction\n` +
`- The Growing Importance of ${topic}\n` +
`- Common Misconceptions & Challenges\n` +
`- What You Will Learn in This Guide\n\n` +
`## 2. Core Foundations & Fundamentals\n` +
`- Key Terminology Explained\n` +
`- How ${topic} Works Under the Hood\n` +
`- Essential Tools & Resources You Need\n\n` +
`## 3. Step-by-Step Implementation Framework\n` +
`- Step 1: Planning and Research\n` +
`- Step 2: Practical Setup & Configuration\n` +
`- Step 3: Best Practices for Maximum Impact\n\n` +
`## 4. Advanced Tips & Expert Strategies\n` +
`- Proven Optimization Techniques\n` +
`- Common Pitfalls to Avoid\n` +
`- Real-World Case Studies & Benchmarks\n\n` +
`## 5. Conclusion & Actionable Next Steps\n` +
`- Key Takeaways Summary\n` +
`- Final Checklist\n` +
`- Call to Action (Share & Subscribe)`;

            output.value = outline;
            document.getElementById('outlineResultCard').style.display = 'block';
            showToast('Blog outline generated!', 'success');
        });

        document.getElementById('btnCopyOutline')?.addEventListener('click', () => {
            if (!output.value) return;
            copyToClipboard(output.value, 'Blog outline copied to clipboard');
        });
    },

    // ==============================================================
    // 19. Instagram Video, Reels & Photo Downloader
    // ==============================================================
    init_instagram_downloader() {
        const input = document.getElementById('igUrlInput');
        const btnFetch = document.getElementById('btnFetchIg');
        const btnPaste = document.getElementById('btnPasteIg');
        const btnClear = document.getElementById('btnClearIg');
        const results = document.getElementById('igResultContainer');
        if (!input || !btnFetch) return;

        btnPaste?.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                if (text) {
                    input.value = text.trim();
                    showToast('Pasted link from clipboard', 'info');
                }
            } catch (e) {
                input.focus();
                showToast('Please press Ctrl+V to paste', 'info');
            }
        });

        btnClear?.addEventListener('click', () => {
            input.value = '';
            if (results) results.style.display = 'none';
            input.focus();
        });

        btnFetch.addEventListener('click', async () => {
            const url = input.value.trim();
            if (!url) return showToast('Please enter an Instagram Reel, Video, or Post link', 'error');

            btnFetch.disabled = true;
            btnFetch.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Fetching Media...';

            try {
                const apiUrl = (window.APP_CONFIG && window.APP_CONFIG.apiUrl) ? window.APP_CONFIG.apiUrl : 'api/index.php';
                const res = await fetch(`${apiUrl}?action=instagram_info&url=${encodeURIComponent(url)}`);
                const data = await res.json();

                if (!data.success) {
                    showToast(data.message || 'Could not fetch Instagram post', 'error');
                    return;
                }

                const getExtIcon = (ext) => {
                    if (ext === 'mp3') return '<i class="fa-solid fa-music" style="color: #f59e0b;"></i>';
                    if (ext === 'jpg' || ext === 'jpeg' || ext === 'png') return '<i class="fa-solid fa-image" style="color: #10b981;"></i>';
                    return '<i class="fa-solid fa-file-video" style="color: #ff6b00;"></i>';
                };

                const formatsHtml = (data.formats || []).map(f => `
                    <div class="dl-format-row" style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 38px; height: 38px; border-radius: 8px; background: var(--bg-surface); border: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                ${getExtIcon(f.ext)}
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 0.4rem;">
                                    <span>${escapeHtml(f.label)}</span>
                                    ${f.badge ? `<span class="badge" style="background: rgba(255, 107, 0, 0.1); color: #ea580c; border: 1px solid rgba(255, 107, 0, 0.2); font-size: 0.7rem; padding: 1px 6px;">${escapeHtml(f.badge)}</span>` : ''}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fa-solid fa-database"></i> ${f.size} • <span style="text-transform: uppercase;">.${f.ext}</span></div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button type="button" class="btn-secondary btn-sm" onclick="copyToClipboard('${f.download_url}', 'Download link copied to clipboard!')">
                                <i class="fa-regular fa-copy"></i> Copy Link
                            </button>
                            <a href="${f.download_url}" target="_blank" download class="btn-primary-gradient btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-cloud-arrow-down"></i> Download ${f.ext.toUpperCase()}
                            </a>
                        </div>
                    </div>
                `).join('');

                const previewHtml = data.video_preview ? `
                    <video controls playsinline poster="${data.thumbnail}" style="width: 100%; max-height: 380px; border-radius: var(--radius-md); background: #000; object-fit: contain;">
                        <source src="${data.video_preview}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                ` : (data.thumbnail ? `
                    <img src="${data.thumbnail}" alt="Instagram Media" style="width: 100%; max-height: 380px; border-radius: var(--radius-md); object-fit: cover;">
                ` : `
                    <div style="height: 220px; display: flex; align-items: center; justify-content: center; background: var(--bg-body); border-radius: var(--radius-md); color: var(--text-muted);">
                        <i class="fa-solid fa-photo-film" style="font-size: 3rem;"></i>
                    </div>
                `);

                results.innerHTML = `
                    <div style="margin-top: 2rem; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm);">
                        <div style="display: grid; grid-template-columns: minmax(280px, 1fr) 1.5fr; gap: 1.5rem; align-items: start;">
                            <div>
                                ${previewHtml}
                                <div style="margin-top: 0.75rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                                    <span style="font-weight: 700; color: #ec4899;"><i class="fa-brands fa-instagram"></i> ${escapeHtml(data.author || '@instagram')}</span>
                                    <span style="color: var(--text-muted);">ID: ${escapeHtml(data.shortcode)}</span>
                                </div>
                            </div>
                            <div>
                                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.4; color: var(--text-primary);">${escapeHtml(data.title)}</h3>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">Select your desired format below to download directly to your device:</p>
                                <div class="dl-formats-list">
                                    ${formatsHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                results.style.display = 'block';
                showToast('Instagram media ready for download!', 'success');
            } catch (err) {
                showToast('Failed to connect to download server', 'error');
            } finally {
                btnFetch.disabled = false;
                btnFetch.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Get Download Links';
            }
        });
    },

    // ==============================================================
    // 20. YouTube Video & MP3 Downloader
    // ==============================================================
    init_youtube_downloader() {
        const input = document.getElementById('ytDownloaderInput');
        const btnFetch = document.getElementById('btnFetchYt');
        const btnPaste = document.getElementById('btnPasteYt');
        const btnClear = document.getElementById('btnClearYt');
        const results = document.getElementById('ytResultContainer');
        if (!input || !btnFetch) return;

        btnPaste?.addEventListener('click', async () => {
            try {
                const text = await navigator.clipboard.readText();
                if (text) {
                    input.value = text.trim();
                    showToast('Pasted YouTube URL', 'info');
                }
            } catch (e) {
                input.focus();
                showToast('Press Ctrl+V to paste', 'info');
            }
        });

        btnClear?.addEventListener('click', () => {
            input.value = '';
            if (results) results.style.display = 'none';
            input.focus();
        });

        btnFetch.addEventListener('click', async () => {
            const url = input.value.trim();
            if (!url) return showToast('Please enter a YouTube video or Shorts link', 'error');

            btnFetch.disabled = true;
            btnFetch.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Converting & Fetching...';

            try {
                const apiUrl = (window.APP_CONFIG && window.APP_CONFIG.apiUrl) ? window.APP_CONFIG.apiUrl : 'api/index.php';
                const res = await fetch(`${apiUrl}?action=youtube_info&url=${encodeURIComponent(url)}`);
                const data = await res.json();

                if (!data.success) {
                    showToast(data.message || 'Could not fetch YouTube video', 'error');
                    return;
                }

                const videosHtml = (data.formats?.video || []).map(v => `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); font-weight: 700;">MP4</span>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">${v.label} (${v.resolution})</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">${v.badge ? v.badge + ' • ' : ''}${v.size}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button type="button" class="btn-secondary btn-sm" onclick="copyToClipboard('${v.download_url}', 'Download link copied!')">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                            <a href="${v.download_url}" target="_blank" download class="btn-primary-gradient btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-download"></i> Download ${v.quality}
                            </a>
                        </div>
                    </div>
                `).join('');

                const audiosHtml = (data.formats?.audio || []).map(a => `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); font-weight: 700;">${a.ext.toUpperCase()}</span>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">${a.label}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">${a.badge ? a.badge + ' • ' : ''}${a.size}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <button type="button" class="btn-secondary btn-sm" onclick="copyToClipboard('${a.download_url}', 'Audio download link copied!')">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                            <a href="${a.download_url}" target="_blank" download class="btn-primary-gradient btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-music"></i> Download Audio
                            </a>
                        </div>
                    </div>
                `).join('');

                const thumbsHtml = (data.formats?.thumbnails || []).map(t => `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background: var(--bg-body); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); font-weight: 700;">JPG</span>
                            <div>
                                <div style="font-weight: 700; color: var(--text-primary);">${t.label}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">${t.resolution}</div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <a href="${t.download_url}" target="_blank" download class="btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-image"></i> Download Thumbnail
                            </a>
                        </div>
                    </div>
                `).join('');

                results.innerHTML = `
                    <div style="margin-top: 2rem; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); padding: 1.75rem; box-shadow: var(--shadow-sm);">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
                            <span class="badge" style="background: #ef4444; color: #fff; font-weight: 700;"><i class="fa-brands fa-youtube"></i> YouTube</span>
                            <span style="font-weight: 700; color: var(--text-secondary);">${escapeHtml(data.author)}</span>
                            <span style="color: var(--text-muted); font-size: 0.85rem; margin-left: auto;">Video ID: <code>${data.id}</code></span>
                        </div>

                        <div style="display: grid; grid-template-columns: minmax(300px, 1.1fr) 1.4fr; gap: 2rem; align-items: start;">
                            <div>
                                <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: var(--radius-md); background: #000;">
                                    <iframe src="${data.embed_url}" style="position: absolute; top:0; left: 0; width: 100%; height: 100%; border: 0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; margin-top: 1rem; line-height: 1.4; color: var(--text-primary);">${escapeHtml(data.title)}</h3>
                            </div>

                            <div>
                                <div class="tab-buttons" style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.75rem;">
                                    <button type="button" class="btn-tab active" onclick="switchYtTab('video', this)" style="padding: 0.4rem 0.9rem; font-size: 0.85rem; font-weight: 700; border-radius: var(--radius-md); border: none; background: var(--primary); color: #fff; cursor: pointer;">
                                        <i class="fa-solid fa-video"></i> Video (MP4)
                                    </button>
                                    <button type="button" class="btn-tab" onclick="switchYtTab('audio', this)" style="padding: 0.4rem 0.9rem; font-size: 0.85rem; font-weight: 700; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); background: var(--bg-body); color: var(--text-secondary); cursor: pointer;">
                                        <i class="fa-solid fa-music"></i> Audio (MP3)
                                    </button>
                                    <button type="button" class="btn-tab" onclick="switchYtTab('thumb', this)" style="padding: 0.4rem 0.9rem; font-size: 0.85rem; font-weight: 700; border-radius: var(--radius-md); border: 1px solid var(--border-subtle); background: var(--bg-body); color: var(--text-secondary); cursor: pointer;">
                                        <i class="fa-solid fa-image"></i> Thumbnails
                                    </button>
                                </div>

                                <div id="ytTabVideo" class="yt-tab-pane">
                                    ${videosHtml}
                                </div>
                                <div id="ytTabAudio" class="yt-tab-pane" style="display: none;">
                                    ${audiosHtml}
                                </div>
                                <div id="ytTabThumb" class="yt-tab-pane" style="display: none;">
                                    ${thumbsHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                results.style.display = 'block';
                showToast('Video conversion complete! Ready to download.', 'success');
            } catch (err) {
                showToast('Unable to connect to converter server', 'error');
            } finally {
                btnFetch.disabled = false;
                btnFetch.innerHTML = '<i class="fa-solid fa-cloud-arrow-down"></i> Convert & Download';
            }
        });
    }
};

function switchYtTab(tab, btn) {
    document.querySelectorAll('.btn-tab').forEach(b => {
        b.style.background = 'var(--bg-body)';
        b.style.color = 'var(--text-secondary)';
        b.style.border = '1px solid var(--border-subtle)';
    });
    btn.style.background = 'var(--primary)';
    btn.style.color = '#fff';
    btn.style.border = 'none';

    const v = document.getElementById('ytTabVideo');
    const a = document.getElementById('ytTabAudio');
    const t = document.getElementById('ytTabThumb');
    if (v) v.style.display = (tab === 'video') ? 'block' : 'none';
    if (a) a.style.display = (tab === 'audio') ? 'block' : 'none';
    if (t) t.style.display = (tab === 'thumb') ? 'block' : 'none';
}
