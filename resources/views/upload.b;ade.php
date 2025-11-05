<!doctype html>
<html>
<head>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Upload Large Movie</title>
</head>
<body>
  <h2>Upload large movie file (GBs supported)</h2>
  <input type="file" id="fileInput">
  <button id="uploadBtn">Upload</button>
  <progress id="progress" value="0" max="100" style="width:600px;"></progress>
  <div id="status"></div>

<script>
const chunkSize = 25 * 1024 * 1024; // 25 MB per chunk

document.getElementById('uploadBtn').onclick = async () => {
  const file = document.getElementById('fileInput').files[0];
  if (!file) return alert('Choose a file');
  const identifier = `${file.name}-${file.size}-${file.lastModified}`;
  const totalChunks = Math.ceil(file.size / chunkSize);
  const token = document.querySelector('meta[name="csrf-token"]').content;

  for (let i=0;i<totalChunks;i++){
    const start=i*chunkSize,end=Math.min(start+chunkSize,file.size);
    const form=new FormData();
    form.append('file', file.slice(start,end), file.name);
    form.append('identifier',identifier);
    form.append('filename',file.name);
    form.append('chunkIndex',i);
    form.append('totalChunks',totalChunks);

    const res=await fetch('/upload-chunk',{method:'POST',headers:{'X-CSRF-TOKEN':token},body:form});
    if(!res.ok){document.getElementById('status').innerText='Chunk '+i+' failed';return;}
    document.getElementById('progress').value=((i+1)/totalChunks)*100;
  }

  const res2=await fetch('/upload-complete',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':token},
    body:JSON.stringify({identifier,filename:file.name,totalChunks})
  });
  const data=await res2.json();
  document.getElementById('status').innerText='Done! '+JSON.stringify(data);
};
</script>
</body>
</html>
