import { ref } from 'vue';

export interface ConfirmOptions {
    title: string;
    description: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}

// Singleton state shared across the app
const isOpen = ref(false);
const options = ref<ConfirmOptions>({
    title: '',
    description: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'default',
});
let resolvePromise: ((value: boolean) => void) | null = null;

export function useConfirm() {
    const confirm = (opts: ConfirmOptions): Promise<boolean> => {
        options.value = {
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            variant: 'default',
            ...opts,
        };
        isOpen.value = true;

        return new Promise((resolve) => {
            resolvePromise = resolve;
        });
    };

    const handleConfirm = () => {
        isOpen.value = false;
        resolvePromise?.(true);
        resolvePromise = null;
    };

    const handleCancel = () => {
        isOpen.value = false;
        resolvePromise?.(false);
        resolvePromise = null;
    };

    return {
        isOpen,
        options,
        confirm,
        handleConfirm,
        handleCancel,
    };
}
