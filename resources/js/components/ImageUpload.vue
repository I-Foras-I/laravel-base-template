<script setup lang="ts">
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Upload, X } from 'lucide-vue-next';

const props = defineProps<{
    modelValue?: File | null;
    currentImageUrl?: string;
    defaultInitials?: string;
}>();

const emit = defineEmits(['update:modelValue']);

const previewUrl = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];
        emit('update:modelValue', file);
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewUrl.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const removePhoto = () => {
    emit('update:modelValue', null);
    previewUrl.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <div class="flex items-center gap-4">
        <Avatar class="h-16 w-16">
            <AvatarImage :src="previewUrl || currentImageUrl || ''" />
            <AvatarFallback>{{ defaultInitials || 'UA' }}</AvatarFallback>
        </Avatar>
        
        <div class="flex gap-2">
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                accept="image/*"
                @change="handleFileChange"
            />
            <Button type="button" variant="outline" size="sm" @click="triggerFileInput">
                <Upload class="mr-2 h-4 w-4" />
                Upload Photo
            </Button>
            <Button 
                v-if="previewUrl" 
                type="button" 
                variant="ghost" 
                size="sm" 
                @click="removePhoto"
            >
                <X class="mr-2 h-4 w-4" />
                Cancel
            </Button>
        </div>
    </div>
</template>
