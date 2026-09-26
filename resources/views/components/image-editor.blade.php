<div
    class="grid gap-3 rounded-2xl border border-twende-line p-4 dark:border-white/10"
    x-data="{
        text: '',
        color: '#F20205',
        size: 42,
        dirty: false,
        angle: 0,
        source: null,
        init() {
            this.$root.closest('form')?.addEventListener('submit', (event) => this.apply(event));
        },
        load(event) {
            const file = event.target.files[0];
            if (! file) return;
            this.source = file;
            const reader = new FileReader();
            reader.onload = () => {
                const image = new Image();
                image.onload = () => {
                    this.draw(image, false);
                    this.$refs.canvas.dataset.ready = '1';
                    this.$refs.canvas._image = image;
                };
                image.src = reader.result;
            };
            reader.readAsDataURL(file);
        },
        draw(image, stamp) {
            const canvas = this.$refs.canvas;
            const max = 900;
            const scale = Math.min(1, max / Math.max(image.width, image.height));
            canvas.width = Math.max(1, Math.round(image.width * scale));
            canvas.height = Math.max(1, Math.round(image.height * scale));
            const context = canvas.getContext('2d');
            context.save();
            if (this.angle % 180 !== 0) {
                canvas.width = Math.max(1, Math.round(image.height * scale));
                canvas.height = Math.max(1, Math.round(image.width * scale));
            }
            context.translate(canvas.width / 2, canvas.height / 2);
            context.rotate(this.angle * Math.PI / 180);
            context.drawImage(image, -image.width * scale / 2, -image.height * scale / 2, image.width * scale, image.height * scale);
            context.restore();
            if (stamp && this.text.trim() !== '') {
                context.font = '700 ' + this.size + 'px Outfit, sans-serif';
                context.fillStyle = this.color;
                context.textAlign = 'center';
                context.fillText(this.text, canvas.width / 2, canvas.height / 2);
                this.dirty = true;
            }
        },
        rotate() {
            if (! this.$refs.canvas._image) return;
            this.angle = (this.angle + 90) % 360;
            this.draw(this.$refs.canvas._image, false);
        },
        stamp() {
            if (! this.$refs.canvas._image) return;
            this.draw(this.$refs.canvas._image, true);
        },
        async apply(event) {
            const canvas = this.$refs.canvas;
            if (! this.dirty || ! canvas.dataset.ready || ! this.source) return;
            event.preventDefault();
            const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9));
            const edited = new File([blob], 'edited.jpg', { type: 'image/jpeg' });
            const imageInput = this.$refs.file;
            const original = this.$refs.original;
            const editedList = new DataTransfer();
            editedList.items.add(edited);
            imageInput.files = editedList.files;
            const originalList = new DataTransfer();
            originalList.items.add(this.source);
            original.files = originalList.files;
            this.dirty = false;
            event.target.requestSubmit();
        },
    }"
>
    <p class="text-sm text-twende-muted">{{ __('experience.editor_help') }}</p>
    <input x-ref="file" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="text-sm" x-on:change="load($event)">
    <input x-ref="original" name="original" type="file" class="hidden" tabindex="-1">
    <canvas x-ref="canvas" class="max-h-72 max-w-full rounded-xl bg-twende-light dark:bg-white/5"></canvas>
    <div class="flex flex-wrap gap-2">
        <button type="button" class="h-10 rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15" x-on:click="rotate()">{{ __('experience.editor_rotate') }}</button>
        <input x-model="text" type="text" maxlength="40" placeholder="{{ __('experience.editor_text') }}" class="h-10 min-w-0 flex-1 rounded-full border border-twende-line px-4 text-sm dark:border-white/15 dark:bg-twende-night">
        <input x-model="color" type="color" class="h-10 w-12 rounded-full border border-twende-line" aria-label="{{ __('experience.hex') }}">
        <button type="button" class="h-10 rounded-full bg-twende-red px-4 text-sm font-semibold text-white" x-on:click="stamp()">{{ __('experience.editor_apply') }}</button>
        <button type="button" class="h-10 rounded-full border border-twende-line px-3 text-sm dark:border-white/15" x-on:click="text = '{{ __('experience.badge_promo') }}'; stamp()">{{ __('experience.badge_promo') }}</button>
        <button type="button" class="h-10 rounded-full border border-twende-line px-3 text-sm dark:border-white/15" x-on:click="text = '{{ __('experience.badge_new') }}'; stamp()">{{ __('experience.badge_new') }}</button>
    </div>
</div>
