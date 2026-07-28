/*
 * Hand-written XHR bulk uploader for the /onboarding/v2 wizard — not
 * Livewire's uploadMultiple(), which puts every file in one POST and dies
 * on the server's max_file_uploads=20 limit. One request per file against
 * the signed URL from Phase 2's OnboardingUploadController, up to 4
 * concurrent, each with its own progress bar and retry button so a network
 * blip on one file never touches the others.
 *
 * window.initOnboardingUploader(rootEl) is called via Alpine's x-init on
 * the dropzone element every time Livewire (re)creates it, so this keeps
 * working across step transitions without any Livewire-specific glue code.
 */
(function () {
    var MAX_CONCURRENT = 4;

    window.initOnboardingUploader = function (root) {
        if (root.dataset.obInit === '1') {
            return;
        }
        root.dataset.obInit = '1';

        var uploadUrl = root.dataset.uploadUrl;
        var input = root.querySelector('.ob-dropzone-input');
        var uploadsContainer = root.querySelector('.ob-dropzone-uploads');
        var queue = [];
        var active = 0;

        function pump() {
            while (active < MAX_CONCURRENT && queue.length > 0) {
                var item = queue.shift();
                active++;
                upload(item);
            }
        }

        function enqueue(file) {
            var row = document.createElement('div');
            row.className = 'ob-upload-row';
            row.innerHTML =
                '<span class="ob-upload-name"></span>' +
                '<div class="ob-upload-progress-track"><div class="ob-upload-progress-fill" style="width:0%"></div></div>' +
                '<span class="ob-upload-status">Queued</span>' +
                '<button type="button" class="ob-upload-retry" hidden>Retry</button>';
            row.querySelector('.ob-upload-name').textContent = file.name;
            uploadsContainer.appendChild(row);

            var item = { file: file, row: row };

            row.querySelector('.ob-upload-retry').addEventListener('click', function () {
                row.querySelector('.ob-upload-retry').hidden = true;
                row.querySelector('.ob-upload-status').textContent = 'Queued';
                queue.push(item);
                pump();
            });

            queue.push(item);
            pump();
        }

        function upload(item) {
            var fill = item.row.querySelector('.ob-upload-progress-fill');
            var status = item.row.querySelector('.ob-upload-status');
            var retryBtn = item.row.querySelector('.ob-upload-retry');
            status.textContent = 'Uploading…';
            item.row.classList.remove('ob-upload-row--failed');

            var xhr = new XMLHttpRequest();
            var formData = new FormData();
            formData.append('file', item.file);

            xhr.upload.addEventListener('progress', function (e) {
                if (e.lengthComputable) {
                    fill.style.width = Math.round((e.loaded / e.total) * 100) + '%';
                }
            });

            xhr.addEventListener('load', function () {
                active--;

                if (xhr.status >= 200 && xhr.status < 300) {
                    var body = {};
                    try {
                        body = JSON.parse(xhr.responseText);
                    } catch (e) {
                        /* non-JSON success response — treat as done */
                    }

                    if (body.status === 'duplicate') {
                        status.textContent = 'Already added';
                    } else if (body.status === 'quota_exceeded') {
                        status.textContent = 'Upload limit reached';
                        item.row.classList.add('ob-upload-row--failed');
                    } else {
                        fill.style.width = '100%';
                        status.textContent = 'Done';
                    }

                    if (window.Livewire) {
                        window.Livewire.dispatch('file-uploaded');
                    }
                } else {
                    status.textContent = 'Failed — try again';
                    item.row.classList.add('ob-upload-row--failed');
                    retryBtn.hidden = false;
                }

                pump();
            });

            xhr.addEventListener('error', function () {
                active--;
                status.textContent = 'Network error — try again';
                item.row.classList.add('ob-upload-row--failed');
                retryBtn.hidden = false;
                pump();
            });

            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        }

        function handleFiles(fileList) {
            Array.prototype.forEach.call(fileList, enqueue);
        }

        input.addEventListener('change', function () {
            handleFiles(input.files);
            input.value = '';
        });

        root.addEventListener('dragover', function (e) {
            e.preventDefault();
            root.classList.add('ob-dropzone--active');
        });
        root.addEventListener('dragleave', function () {
            root.classList.remove('ob-dropzone--active');
        });
        root.addEventListener('drop', function (e) {
            e.preventDefault();
            root.classList.remove('ob-dropzone--active');
            if (e.dataTransfer && e.dataTransfer.files) {
                handleFiles(e.dataTransfer.files);
            }
        });
    };
})();
