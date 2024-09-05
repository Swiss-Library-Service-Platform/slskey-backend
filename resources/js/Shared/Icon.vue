<template>
    <div class="shrink-0" :class="$attrs.class">
        <DocumentDownloadIcon v-if="icon === 'documentDownload'" />
        <TrashIcon v-if="icon === 'trash'" />
        <SaveIcon v-if="icon === 'save'" />
        <ViewListIcon v-if="icon === 'view-list'" />
        <DocumentIcon v-if="icon === 'document'" />
        <UserAddIcon v-if="icon === 'user-add'" />
        <UserRemoveIcon v-if="icon === 'user-remove'" />
        <PencilIcon v-if="icon === 'pencil'" />
        <ChevronLeftIcon v-if="icon === 'chevron-left'" />
        <ChevronRightIcon v-if="icon === 'chevron-right'" />
        <PlusIcon v-if="icon === 'plus'" />
        <FingerPrintIcon v-if="icon === 'finger-print'" />
        <SearchIcon v-if="icon === 'search'" />
        <LibraryIcon v-if="icon === 'library'" />
        <AtSymbolIcon v-if="icon === 'at-symbol'" />
        <UserCircleIcon v-if="icon === 'user-circle'" />
        <PencilAltIcon v-if="icon === 'pencil-alt'" />
        <PhoneIcon v-if="icon === 'phone'" />
        <UserIcon v-if="icon === 'user'" />
        <IdentificationIcon v-if="icon === 'identification'" />
        <MinusCircleIcon v-if="icon === 'minus-circle'" />
        <XIcon v-if="icon === 'x'" />
        <ClockIconOutline v-if="icon === 'clock'" />
        <ClockIcon v-if="icon === 'clock-solid'" />
        <KeyIcon v-if="icon === 'key'" />
        <CogIcon v-if="icon === 'cog'" />
        <DocumentReportIcon v-if="icon === 'report'" />
        <UsersIcon v-if="icon === 'users'" />
        <CheckIcon v-if="icon === 'check'" />
        <StopIcon v-if="icon === 'stop'" />
        <BanIcon v-if="icon === 'ban'" />
        <DotsHorizontalIcon v-if="icon === 'dots'" />
        <CheckCircleIcon v-if="icon === 'check-circle'" />
        <XCircleIcon v-if="icon === 'x-circle'" />
        <ArrowRightIcon v-if="icon === 'arrow'" />
        <UploadIcon v-if="icon === 'upload'" />
        <ArrowSmDownIcon v-if="icon === 'arrow-down'" />
        <ArrowSmUpIcon v-if="icon === 'arrow-up'" />
        <ArrowCircleRightIcon v-if="icon === 'arrow-circle-right'" />
        <HandIcon v-if="icon === 'hand'" />
        <CursorClickIcon v-if="icon === 'cursor-click'" />
        <RefreshIcon v-if="icon === 'refresh'" />
        <ChevronDoubleRightIcon v-if="icon === 'chevron-double-right'" />
        <ChipIcon v-if="icon === 'chip'" />
        <QuestionMarkCircleIcon v-if="icon === 'question-mark'" />
        <LogoutIcon v-if="icon === 'logout'" />
        <MailIcon v-if="icon === 'mail'" />
        <ExclamationIcon v-if="icon === 'exclamation'" />
        <ExclamationCircleIcon v-if="icon === 'exclamation-circle'" />
        <ChevronDownIcon v-if="icon === 'chevron-down'" />
        <BookOpenIcon v-if="icon === 'book-open'" />
        <LightningBoltIcon v-if="icon === 'lightning-bolt'" />
        <LinkIcon v-if="icon === 'link'" />
        <AnnotationIcon v-if="icon === 'annotation'" />
        <LockClosedIcon v-if="icon === 'lock-closed'" />
        <GlobeAltIcon v-if="icon === 'globe-alt'" />
        <InformationCircleIcon v-if="icon === 'information-circle'" />
        <ChartSquareBarIcon v-if="icon === 'chart-square-bar'" />
        <CloudIcon v-if="icon === 'cloud'" />
        <AcademicCapIcon v-if="icon === 'academic-cap'" />
    </div>
</template>

<script>
import {
    ChevronLeftIcon, ChevronRightIcon, PlusIcon, FingerPrintIcon,
    SearchIcon, ClockIcon, ExclamationIcon, ChevronDownIcon, LightningBoltIcon, LinkIcon
} from '@heroicons/vue/solid'
import {
    DocumentDownloadIcon, TrashIcon, SaveIcon, ViewListIcon, PhoneIcon,
    DocumentIcon, UserAddIcon, UserRemoveIcon, PencilIcon, LibraryIcon,
    AtSymbolIcon, UserCircleIcon, PencilAltIcon, UserIcon, IdentificationIcon,
    MinusCircleIcon, ClockIcon as ClockIconOutline, XIcon, KeyIcon, CogIcon, DocumentReportIcon, UsersIcon
    , CheckIcon, StopIcon, BanIcon, DotsHorizontalIcon, CheckCircleIcon, XCircleIcon, ArrowRightIcon, UploadIcon,
    ArrowSmDownIcon, ArrowSmUpIcon, ArrowCircleRightIcon, HandIcon, MailIcon, BookOpenIcon,
    CursorClickIcon, RefreshIcon, ChevronDoubleRightIcon, ChipIcon, QuestionMarkCircleIcon, LogoutIcon, AnnotationIcon,
    LockClosedIcon, InformationCircleIcon, GlobeAltIcon,
    ChartSquareBarIcon, CloudIcon, AcademicCapIcon, ExclamationCircleIcon
} from '@heroicons/vue/outline'
export default {
    components: {
        InformationCircleIcon,
        AnnotationIcon,
        BookOpenIcon,
        LogoutIcon,
        BanIcon,
        CursorClickIcon,
        RefreshIcon,
        ChipIcon,
        CloudIcon,
        HandIcon,
        CheckIcon,
        ChevronLeftIcon,
        AcademicCapIcon,
        ChevronRightIcon,
        ChevronDoubleRightIcon,
        PlusIcon,
        DocumentDownloadIcon,
        TrashIcon,
        StopIcon,
        ArrowCircleRightIcon,
        QuestionMarkCircleIcon,
        SaveIcon,
        SearchIcon,
        ViewListIcon,
        DocumentIcon,
        UserAddIcon,
        MailIcon,
        UserRemoveIcon,
        UsersIcon,
        PencilIcon,
        LibraryIcon,
        AtSymbolIcon,
        UserCircleIcon,
        FingerPrintIcon,
        PencilAltIcon,
        PhoneIcon,
        UserIcon,
        IdentificationIcon,
        MinusCircleIcon,
        XIcon,
        ClockIconOutline,
        ClockIcon,
        KeyIcon,
        CogIcon,
        DocumentReportIcon,
        DotsHorizontalIcon,
        CheckCircleIcon,
        XCircleIcon,
        ArrowRightIcon,
        UploadIcon,
        ArrowSmDownIcon,
        ArrowSmUpIcon,
        ExclamationIcon,
        ChevronDownIcon,
        LightningBoltIcon,
        ExclamationCircleIcon,
        LinkIcon,
        LockClosedIcon,
        ChartSquareBarIcon,
        GlobeAltIcon,
    },
    props: {
        icon: {
            type: String,
            required: true
        }
    }
}
</script>