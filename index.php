

<form id="upload" method="POST" enctype="multipart/form-data" >
    <label for="">Converter csv pra xlsx</label><br>
    <input type="file" name="file[]" accept=".csv, .xlsx" multiple>
    <input type="submit" value="Upload">
</form>

<div id="loading" style="display: none;">Aguarde um momento...</div>
<div id="output-wrapper" style="display: none;">
    <pre id="output"></pre>
</div>

<script>
    const output = document.querySelector('#output');
    const wrapper = document.querySelector('#output-wrapper');
    const formUplod = document.querySelector('#upload');

    function run() {

        const source = new EventSource('stream.php');

        source.onmessage = function(e) {
            console.log(e)
            output.textContent += e.data + '\n';
            output.scrollTop = output.scrollHeight;
        }

        source.addEventListener('done', function(e) {
            console.log(e)
            output.textContent += 'Processo terminado!\n';
            output.scrollTop = output.scrollHeight;
            source.close();
            fetch('download.php')
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.click();
                window.URL.revokeObjectURL(url);
            });
        });

        source.onerror = function(e) {
            console.error('Error:', e);
            output.textContent += 'Error: ' + e.message + '\n';
            source.close();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {


        formUplod.addEventListener('submit', function(e) {
            e.preventDefault();


            const loadingEl = document.querySelector('#loading');
            loadingEl.style.display = 'block';

            let formData = new FormData();
            let fileInput = document.querySelector('input[type="file"]');

            wrapper.style.display = 'block';
            output.textContent = '';

            if(fileInput.files.length === 0) {
                alert('Selecione um arquivo');
                return;
            }

            for (let file of fileInput.files) {
                formData.append('files[]', file);
            }

            wrapper.style.display = 'block';

            fetch('upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text()) // <-- Troque para .text() para debug
            .then(text => {
                console.log('Response:', text); // Veja o erro real no console
                try {
                    const data = JSON.parse(text); // Tente converter para JSON
                    const allSuccess = data.every(item => item.status === 'success');
                    if(!allSuccess) return;
                    loadingEl.style.display = 'none';
                    output.textContent += 'Arquivos enviados com sucesso!\n';
                    setTimeout(() => run(), 300);
                } catch (e) {
                    console.error('Erro ao parsear JSON:', e);
                    output.textContent += 'Erro no servidor (resposta não é JSON)\n';
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
            });

        });

    });
</script>