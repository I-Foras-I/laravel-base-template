<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { FileDown, FileSpreadsheet, FileText, File } from 'lucide-vue-next';

interface Props {
    filters?: {
        search?: string;
        role?: string;
        status?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    filters: () => ({}),
});

const buildExportUrl = (format: 'pdf' | 'excel' | 'csv'): string => {
    const params = new URLSearchParams();
    
    if (props.filters.search) {
        params.append('search', props.filters.search);
    }
    if (props.filters.role && props.filters.role !== 'all') {
        params.append('role', props.filters.role);
    }
    if (props.filters.status && props.filters.status !== 'all') {
        params.append('status', props.filters.status);
    }
    
    const queryString = params.toString();
    const baseUrl = `/admin/users/export/${format}`;
    
    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
};

const exportPdf = () => {
    // Open PDF in new tab
    window.open(buildExportUrl('pdf'), '_blank');
};

const exportExcel = () => {
    // Download Excel file
    window.location.href = buildExportUrl('excel');
};

const exportCsv = () => {
    // Download CSV file
    window.location.href = buildExportUrl('csv');
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="outline" class="gap-2">
                <FileDown class="h-4 w-4" />
                Export
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-48">
            <DropdownMenuLabel>Export Format</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="exportPdf" class="cursor-pointer">
                <File class="mr-2 h-4 w-4 text-red-500" />
                <span>Export as PDF</span>
            </DropdownMenuItem>
            <DropdownMenuItem @click="exportExcel" class="cursor-pointer">
                <FileSpreadsheet class="mr-2 h-4 w-4 text-green-600" />
                <span>Export as Excel</span>
            </DropdownMenuItem>
            <DropdownMenuItem @click="exportCsv" class="cursor-pointer">
                <FileText class="mr-2 h-4 w-4 text-blue-500" />
                <span>Export as CSV</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
