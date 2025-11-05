<!doctype html>
<html>
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta charset="utf-8" />
  <title>Large File Upload</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
  <h3>Upload large file (1GB) — chunked</h3>
  @csrf
  <input type="file" id="fileInput" />
  <button id="uploadBtn">Upload</button>
  <progress id="progress" value="0" max="100" style="width:600px;"></progress>
  <div id="status"></div>

<script>
(async () => {
  const uploadBtn = document.getElementById('uploadBtn');
  const fileInput = document.getElementById('fileInput');
  const progress = document.getElementById('progress');
  const status = document.getElementById('status');

  const chunkSize = 10 * 1024 * 1024; // 10 MB per chunk
  // For 1GB file:
  // 1 GB = 1024 * 1024 * 1024 bytes = 1073741824 bytes
  // Number of chunks = Math.ceil(file.size / chunkSize)

  uploadBtn.addEventListener('click', async () => {
    const file = fileInput.files[0];
    if (!file) { alert('Select file'); return; }

    // Create a unique identifier for this upload (client side)
    const identifier = `${file.name}-${file.size}-${file.lastModified}-${Math.random().toString(36).slice(2,9)}`;

    const totalChunks = Math.ceil(file.size / chunkSize);
    status.innerText = `Uploading ${file.name} in ${totalChunks} chunks...`;

    // Send chunks sequentially (safer). For speed you can parallelize.
    for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
      const start = chunkIndex * chunkSize;
      const end = Math.min(start + chunkSize, file.size);
      const blob = file.slice(start, end);

      const form = new FormData();
      form.append('file', blob, file.name);
      form.append('identifier', identifier);
      form.append('filename', file.name);
      form.append('chunkIndex', chunkIndex);
      form.append('totalChunks', totalChunks);

      // include CSRF token if using web routes
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

      let success = false;
      const maxRetries = 3;
      for (let attempt = 1; attempt <= maxRetries && !success; attempt++) {
        try {
          const res = await fetch('/upload-chunk', {
            method: 'POST',
           headers: { 'X-CSRF-TOKEN': token },
            body: form
          });
          if (!res.ok) throw new Error('Upload failed: ' + res.status);
          const data = await res.json();
          success = true;
        } catch (err) {
          console.warn('Chunk upload error, attempt', attempt, err);
          if (attempt === maxRetries) {
            status.innerText = 'Failed to upload chunk ' + chunkIndex;
            throw err;
          }
          // small backoff
          await new Promise(r => setTimeout(r, 1000 * attempt));
        }
      }

      // update progress
      progress.value = Math.round(((chunkIndex + 1) / totalChunks) * 100);
    }

    // Tell server to assemble
    const assembleRes = await fetch('/upload-complete', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': token
      },
      body: JSON.stringify({
        identifier,
        filename: file.name,
        totalChunks: totalChunks
      })
    });
    const assembleData = await assembleRes.json();
    if (assembleRes.ok) {
      status.innerText = 'Upload complete: ' + JSON.stringify(assembleData);
    } else {
      status.innerText = 'Assembly failed: ' + JSON.stringify(assembleData);
    }
  });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
