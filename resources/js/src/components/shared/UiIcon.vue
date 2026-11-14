<template>
  <component
    :is="iconComponent"
    :size="size"
    :stroke-width="computedStrokeWidth"
    :absolute-stroke-width="true"
    :fill="filled ? 'currentColor' : 'none'"
    :fill-opacity="filled ? 0.16 : undefined"
    :class="['shrink-0', directional ? 'rtl:-scale-x-100' : '']"
    aria-hidden="true"
  />
</template>

<script setup>
import { computed } from 'vue';
import {
  AlertCircle,
  AlertTriangle,
  ArrowLeft,
  ArrowLeftRight,
  ArrowRight,
  BadgeCheck,
  Ban,
  Banknote,
  Bell,
  BellRing,
  BookOpen,
  Calendar,
  Check,
  CheckCircle2,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ClipboardCheck,
  Download,
  ExternalLink,
  Eye,
  EyeOff,
  FileText,
  FlaskConical,
  Handshake,
  HeartHandshake,
  History,
  Inbox,
  Info,
  KeyRound,
  Landmark,
  LayoutDashboard,
  Lightbulb,
  ListX,
  Loader2,
  LockKeyhole,
  LogOut,
  Mail,
  Megaphone,
  Menu,
  MessageSquare,
  Pencil,
  PiggyBank,
  Plus,
  PlusCircle,
  Printer,
  QrCode,
  ReceiptText,
  RefreshCw,
  Save,
  ScanLine,
  School,
  Search,
  Send,
  ShieldCheck,
  Tag,
  Trash2,
  TrendingDown,
  TrendingUp,
  User,
  UserPlus,
  UserX,
  Users,
  Wallet,
  X,
  XCircle,
} from '@lucide/vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  size: {
    type: [Number, String],
    default: 18,
  },
  weight: {
    type: [Number, String],
    default: 500,
  },
  filled: {
    type: Boolean,
    default: false,
  },
  directional: {
    type: Boolean,
    default: false,
  },
});

const ICONS = {
  dashboard: LayoutDashboard,
  home: LayoutDashboard,
  menu: Menu,
  close: X,
  arrow_back: ArrowLeft,
  arrow_forward: ArrowRight,
  chevron_left: ChevronLeft,
  chevron_right: ChevronRight,
  expand_more: ChevronDown,
  keyboard_arrow_down: ChevronDown,
  open_in_new: ExternalLink,
  search: Search,
  sync: RefreshCw,
  refresh: RefreshCw,
  swap_horiz: ArrowLeftRight,
  logout: LogOut,
  save: Save,
  send: Send,
  download: Download,
  print: Printer,
  edit: Pencil,
  delete: Trash2,
  delete_sweep: ListX,
  add: Plus,
  add_circle: PlusCircle,
  add_task: PlusCircle,
  done: Check,
  calendar: Calendar,
  tag: Tag,

  account_balance: Landmark,
  account_balance_wallet: Wallet,
  payments: Banknote,
  savings: PiggyBank,
  money_off: Ban,
  qr_code_2: QrCode,
  qr_code_scanner: ScanLine,
  receipt: ReceiptText,
  receipt_long: ReceiptText,
  fact_check: ClipboardCheck,
  picture_as_pdf: FileText,
  trending_up: TrendingUp,
  trending_down: TrendingDown,

  check_circle: CheckCircle2,
  verified: BadgeCheck,
  cancel: XCircle,
  error: AlertCircle,
  warning: AlertTriangle,
  info: Info,
  lightbulb: Lightbulb,
  progress_activity: Loader2,
  search_off: Search,

  school: School,
  class: BookOpen,
  groups: Users,
  group: User,
  group_off: UserX,
  person: User,
  person_off: UserX,
  person_add: UserPlus,
  admin_panel_settings: ShieldCheck,
  handshake: Handshake,
  volunteer_activism: HeartHandshake,
  forum: MessageSquare,
  mail: Mail,
  campaign: Megaphone,
  notifications: Bell,
  notifications_active: BellRing,
  inbox: Inbox,
  history: History,
  badge: BadgeCheck,
  key: KeyRound,
  lock_reset: LockKeyhole,
  visibility: Eye,
  visibility_off: EyeOff,
  science: FlaskConical,
};

const iconComponent = computed(() => ICONS[props.name] || Info);

const computedStrokeWidth = computed(() => {
  const w = Number(props.weight);
  if (w >= 700) return 2.4;
  if (w >= 500) return 1.95;
  return 1.55;
});
</script>

