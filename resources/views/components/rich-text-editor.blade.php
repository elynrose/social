@props(['name', 'value' => '', 'placeholder' => 'Write your post content...', 'height' => '300px'])

<div class="rich-text-editor" x-data="richTextEditor()">
    <div class="mb-3">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="formatText('bold')" title="Bold">
                <i class="fas fa-bold"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="formatText('italic')" title="Italic">
                <i class="fas fa-italic"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="formatText('underline')" title="Underline">
                <i class="fas fa-underline"></i>
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-list"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" @click.prevent="formatText('bullet')">Bullet List</a></li>
                    <li><a class="dropdown-item" href="#" @click.prevent="formatText('ordered')">Numbered List</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="insertLink()" title="Insert Link">
                <i class="fas fa-link"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" @click="insertEmoji()" title="Insert Emoji">
                😊
            </button>
        </div>
    </div>
    
    <div 
        id="editor-{{ $name }}"
        class="form-control"
        style="height: {{ $height }}; overflow-y: auto;"
        contenteditable="true"
        @input="updateContent"
        @paste="handlePaste"
        x-ref="editor"
    >{!! $value !!}</div>
    
    <textarea 
        name="{{ $name }}" 
        x-ref="textarea" 
        style="display: none;"
    >{{ $value }}</textarea>
    
    <div class="mt-2 d-flex justify-content-between align-items-center">
        <small class="text-muted">
            <span x-text="characterCount"></span> characters
        </small>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary" @click="clearContent">Clear</button>
            <button type="button" class="btn btn-outline-secondary" @click="previewContent">Preview</button>
        </div>
    </div>
</div>

<script>
function richTextEditor() {
    return {
        characterCount: 0,
        init() {
            this.updateCharacterCount();
            this.$refs.editor.addEventListener('focus', () => {
                this.$refs.editor.classList.add('border-primary');
            });
            this.$refs.editor.addEventListener('blur', () => {
                this.$refs.editor.classList.remove('border-primary');
            });
        },
        updateContent() {
            const content = this.$refs.editor.innerHTML;
            this.$refs.textarea.value = content;
            this.updateCharacterCount();
        },
        updateCharacterCount() {
            const text = this.$refs.editor.textContent || '';
            this.characterCount = text.length;
        },
        formatText(command) {
            document.execCommand(command, false, null);
            this.updateContent();
        },
        insertLink() {
            const url = prompt('Enter URL:');
            if (url) {
                document.execCommand('createLink', false, url);
                this.updateContent();
            }
        },
        insertEmoji() {
            const emojis = ['😊', '👍', '❤️', '🎉', '🔥', '💯', '✨', '🚀', '📈', '🎯'];
            const emoji = emojis[Math.floor(Math.random() * emojis.length)];
            document.execCommand('insertText', false, emoji);
            this.updateContent();
        },
        clearContent() {
            if (confirm('Are you sure you want to clear the content?')) {
                this.$refs.editor.innerHTML = '';
                this.updateContent();
            }
        },
        previewContent() {
            const content = this.$refs.editor.innerHTML;
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            document.getElementById('previewContent').innerHTML = content;
            modal.show();
        },
        handlePaste(e) {
            e.preventDefault();
            const text = e.clipboardData.getData('text/plain');
            document.execCommand('insertText', false, text);
            this.updateContent();
        }
    }
}
</script>

<style>
.rich-text-editor [contenteditable="true"] {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.75rem;
    min-height: 200px;
    outline: none;
}

.rich-text-editor [contenteditable="true"]:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.rich-text-editor .btn-group .btn {
    border-radius: 0;
}

.rich-text-editor .btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.rich-text-editor .btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}
</style> 