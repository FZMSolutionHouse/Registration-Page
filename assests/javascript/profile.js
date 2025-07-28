 
        function previewImage(event) {
            const file = event.target.files[0];
            const fileName = document.getElementById('fileName');
            const uploadIcon = document.getElementById('uploadIcon');
            const imagePreview = document.getElementById('imagePreview');
            
            if (file) {
                // Validate file type
                if (!file.type.match('image/jpeg') && !file.type.match('image/jpg')) {
                    alert('Please select a JPG/JPEG image file.');
                    event.target.value = '';
                    return;
                }
                
                // Check image dimensions
                const img = new Image();
                img.onload = function() {
                    if (this.width < 400 || this.height < 500) {
                        alert('Image must be at least 400 x 500 pixels. Current size: ' + this.width + ' x ' + this.height);
                        event.target.value = '';
                        fileName.textContent = 'No file chosen';
                        imagePreview.style.display = 'none';
                        uploadIcon.style.display = 'flex';
                        return;
                    }
                    
                    // Image is valid, show preview
                    fileName.textContent = file.name;
                    imagePreview.src = URL.createObjectURL(file);
                    imagePreview.style.display = 'block';
                    uploadIcon.style.display = 'none';
                };
                
                img.src = URL.createObjectURL(file);
            } else {
                fileName.textContent = 'No file chosen';
                imagePreview.style.display = 'none';
                uploadIcon.style.display = 'flex';
            }
        }

        // Add drag and drop functionality
        const uploadArea = document.querySelector('.upload-area');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#17a2b8';
            uploadArea.style.background = '#e6f7ff';
        });
        
        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#20b2aa';
            uploadArea.style.background = '#f8f9fa';
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#20b2aa';
            uploadArea.style.background = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('fileInput').files = files;
                previewImage({ target: { files: files } });
            }
        });
    