// ===== Dữ liệu mẫu cho màn "Tạo bài giảng V3" (UI-only, mô phỏng prototype) =====

export const RTYPES = [
  { id: 'baidoc', name: 'Bài đọc', unit: 'bài đọc', icon: 'reader', color: '#2563eb' },
  { id: 'sachnoi', name: 'Sách nói', unit: 'sách nói', icon: 'headphones', color: '#7c3aed' },
  { id: 'video', name: 'Video', unit: 'video', icon: 'video', color: '#ea580c' },
  { id: 'baitap', name: 'Bài tập luyện tập', unit: 'bài tập', icon: 'puzzle', color: '#16a34a' },
];

// mỗi bài = 10 câu hỏi với tỉ lệ độ khó cố định
export const BAI_DEFS = [
  { level: 'Dễ', name: 'Bài dễ', mix: { Dễ: 7, 'Trung bình': 2, Khó: 1 } },
  { level: 'Trung bình', name: 'Bài trung bình', mix: { Dễ: 3, 'Trung bình': 5, Khó: 2 } },
  { level: 'Khó', name: 'Bài khó', mix: { Dễ: 1, 'Trung bình': 4, Khó: 5 } },
];
export const BAI_BY_LEVEL = Object.fromEntries(BAI_DEFS.map((b) => [b.level, b]));
export const mixStr = (mix) => `${mix['Dễ']} Dễ · ${mix['Trung bình']} TB · ${mix['Khó']} Khó`;
export const scaleMix = (mix, n) => ({ Dễ: mix['Dễ'] * n, 'Trung bình': mix['Trung bình'] * n, Khó: mix['Khó'] * n });

export const summaryDesc = {
  setup: 'Nội dung, mức độ và số lượng bài tập',
  baidoc: 'Một bài đọc chung do AI tạo',
  sachnoi: 'Một bản thu âm thanh chung',
  video: 'Một video bài giảng chung',
  baitap: 'Mỗi bài tập 10 câu hỏi luyện tập',
};

// ===== Nội dung mẫu cho 1 bài học =====
export const LESSON = {
  title: 'Tôi là học sinh lớp 2',
  subject: 'Tiếng Việt',
  grade: 'Lớp 2',
  level: 'Dễ',
  voice: 'Nova (Nữ trẻ)',
  reading: [
    'Hôm nay là ngày tựu trường. Mới sáng sớm, mẹ đã gọi nhưng tôi đã vùng dậy và chuẩn bị xong mọi thứ thật nhanh. Tôi muốn đến trường sớm nhất lớp.',
    'Trên đường tới trường, ánh nắng tràn ngập sân trường. Tôi chào mẹ rồi chạy ào vào cùng các bạn đang ríu rít trò chuyện.',
    'Tôi thấy các em lớp 1 còn rụt rè níu tay bố mẹ. Nhìn các em, tôi bỗng cảm thấy mình đã lớn bổng lên. Tôi tự hào vì mình là học sinh lớp 2 rồi!',
  ],
  words: 86,
  duration: '2:14',
};

export const READING_VARIANTS = [
  [
    'Sáng nay, trời trong xanh và mát mẻ. Em thức dậy thật sớm vì hôm nay là ngày đầu tiên của năm học mới. Em háo hức đến mức chuẩn bị xong sách vở chỉ trong chốc lát.',
    'Mẹ dắt em tới trường. Cổng trường rộn ràng tiếng cười nói. Em vẫy tay chào mẹ rồi chạy vào sân, nơi các bạn đang tụ tập trò chuyện vui vẻ.',
    'Nhìn các em lớp 1 còn bỡ ngỡ, em thấy mình thật lớn. Em mỉm cười và thầm nhủ: "Mình đã là học sinh lớp 2 rồi!"',
  ],
  [
    'Ngày tựu trường đã đến. Em dậy từ tinh mơ, lòng rộn ràng khó tả. Chỉ một loáng, em đã gọn gàng trong bộ đồng phục mới tinh.',
    'Con đường tới trường hôm nay như ngắn hơn mọi ngày. Sân trường ngập nắng, các bạn ríu rít như đàn chim nhỏ. Em chào mẹ rồi hoà vào niềm vui ấy.',
    'Thấy các em lớp 1 nắm chặt tay bố mẹ, em chợt nhận ra mình đã trưởng thành hơn. Một năm học mới bắt đầu, và em tự hào vì đã là học sinh lớp 2.',
  ],
];

export const VOICE_LIST = [
  { name: 'Nova (Nữ trẻ)', desc: 'Nữ · trẻ trung, rõ ràng', region: 'Trung tính' },
  { name: 'Mai (Nữ miền Bắc)', desc: 'Nữ · ấm, truyền cảm', region: 'Miền Bắc' },
  { name: 'Minh (Nam miền Bắc)', desc: 'Nam · trầm, chắc chắn', region: 'Miền Bắc' },
  { name: 'Lan (Nữ miền Nam)', desc: 'Nữ · nhẹ nhàng, thân thiện', region: 'Miền Nam' },
  { name: 'Tuấn (Nam miền Nam)', desc: 'Nam · vui tươi, gần gũi', region: 'Miền Nam' },
];
export const SPEEDS = ['Chậm', 'Vừa', 'Nhanh'];
export const DURATIONS = ['2:14', '1:58', '2:30', '2:06'];

// ===== Video =====
export const VSTYLES = ['Hoạt hình minh hoạ', 'Giáo viên ảo (AI avatar)', 'Slide thuyết minh'];
export const VDURATIONS = ['1:48', '2:05', '1:32', '2:20'];
export const VSCENES = [
  { t: '0:00', label: 'Mở đầu — Giới thiệu bài học' },
  { t: '0:18', label: 'Cảnh 1 — Buổi sáng tựu trường' },
  { t: '0:46', label: 'Cảnh 2 — Trên đường tới trường' },
  { t: '1:14', label: 'Cảnh 3 — Niềm tự hào của bạn nhỏ' },
  { t: '1:36', label: 'Tổng kết — Bài học rút ra' },
];

// ===== Câu hỏi mẫu =====
export const QUESTIONS = [
  {
    id: 1, kind: 'chon', level: 'Dễ', text: 'Bạn nhỏ trong bài đọc cảm thấy thế nào khi đến trường?',
    options: [{ t: 'Sợ hãi, lo lắng' }, { t: 'Mình đã lớn bổng lên', correct: true }, { t: 'Buồn bã, nhớ nhà' }, { t: 'Mệt mỏi, chán nản' }],
  },
  {
    id: 2, kind: 'chon', level: 'Dễ', text: 'Ai đã gọi bạn nhỏ dậy vào buổi sáng tựu trường?',
    options: [{ t: 'Bố' }, { t: 'Mẹ', correct: true }, { t: 'Bà' }, { t: 'Anh trai' }],
  },
  {
    id: 3, kind: 'sapxep', level: 'Trung bình', text: 'Sắp xếp các sự việc theo đúng trình tự trong bài đọc "Tôi là học sinh lớp 2".',
    seq: [
      'Sáng sớm, mẹ gọi, bạn nhỏ vùng dậy và chuẩn bị xong mọi thứ rất nhanh.',
      'Bạn nhỏ chào mẹ rồi chạy ào vào cùng các bạn đang ríu rít trong sân trường.',
      'Bạn nhỏ thấy các em lớp 1 rụt rè níu tay bố mẹ và cảm thấy mình lớn bổng lên.',
    ],
  },
  {
    id: 4, kind: 'sapxep', level: 'Trung bình', text: 'Sắp xếp các từ sau thành câu hoàn chỉnh.',
    seq: ['Ánh nắng', 'tràn ngập', 'sân trường.'],
  },
  {
    id: 5, kind: 'noi', level: 'Khó', text: 'Nối từ ngữ ở cột A với nghĩa phù hợp ở cột B.',
    pairs: [['ríu rít', 'nói chuyện vui vẻ, liền nhau'], ['rụt rè', 'e dè, chưa mạnh dạn'], ['vùng dậy', 'bật dậy thật nhanh']],
  },
  {
    id: 6, kind: 'chon', level: 'Khó', text: 'Theo em, vì sao bạn nhỏ lại cảm thấy "mình đã lớn bổng lên"?',
    options: [{ t: 'Vì bạn cao hơn năm ngoái' }, { t: 'Vì bạn thấy mình trưởng thành hơn các em lớp 1', correct: true }, { t: 'Vì bạn được mặc áo mới' }, { t: 'Vì bạn đến trường sớm' }],
  },
];
export const KIND_LABEL = { chon: 'Chọn', sapxep: 'Sắp xếp', noi: 'Nối' };

export const LEVEL_STYLE = {
  Dễ: { bg: '#e8f7ee', color: '#15803d' },
  'Trung bình': { bg: '#fdf3e0', color: '#b45309' },
  Khó: { bg: '#fde8e6', color: '#c2410c' },
};

// 10 câu cho 1 bài theo mix
export function buildBaiQuestions(mix) {
  const out = [];
  let uid = 0;
  ['Dễ', 'Trung bình', 'Khó'].forEach((lv) => {
    const pool = QUESTIONS.filter((q) => q.level === lv);
    for (let i = 0; i < (mix[lv] || 0); i++) {
      out.push({ ...pool[i % pool.length], uid: `${lv}-${uid++}` });
    }
  });
  return out;
}

// ===== Giao bài cho học sinh =====
export const GROUPS = [
  { id: 'yeu', name: 'Nhóm yếu', sug: 'Dễ', color: '#15803d', bg: '#e8f7ee' },
  { id: 'kha', name: 'Nhóm khá', sug: 'Trung bình', color: '#b45309', bg: '#fdf3e0' },
  { id: 'gioi', name: 'Nhóm giỏi', sug: 'Khó', color: '#c2410c', bg: '#fde8e6' },
  { id: 'lop', name: 'Cả lớp', sug: null, color: '#1d63c9', bg: '#e6efff' },
];
export const GROUP_BY_ID = Object.fromEntries(GROUPS.map((g) => [g.id, g]));
export const GRP_ORDER = ['yeu', 'kha', 'gioi'];

export const CLASSES = [
  { id: '2a', name: 'Lớp 2A' },
  { id: '2b', name: 'Lớp 2B' },
  { id: '2c', name: 'Lớp 2C' },
];
export const ROSTER = {
  '2a': [
    { id: 'a1', name: 'Nguyễn Bảo An', grp: 'yeu' }, { id: 'a2', name: 'Trần Gia Bình', grp: 'yeu' },
    { id: 'a3', name: 'Lê Minh Châu', grp: 'yeu' }, { id: 'a4', name: 'Phạm Hải Đăng', grp: 'yeu' },
    { id: 'a5', name: 'Vũ Khánh Hà', grp: 'kha' }, { id: 'a6', name: 'Đỗ Gia Hân', grp: 'kha' },
    { id: 'a7', name: 'Bùi Tuấn Kiệt', grp: 'kha' }, { id: 'a8', name: 'Hoàng Bảo Lâm', grp: 'kha' },
    { id: 'a9', name: 'Ngô Thảo My', grp: 'kha' }, { id: 'a10', name: 'Dương Minh Quân', grp: 'gioi' },
    { id: 'a11', name: 'Đặng Yến Nhi', grp: 'gioi' }, { id: 'a12', name: 'Lý Gia Huy', grp: 'gioi' },
  ],
  '2b': [
    { id: 'b1', name: 'Trịnh Bảo Long', grp: 'yeu' }, { id: 'b2', name: 'Mai Phương Linh', grp: 'yeu' },
    { id: 'b3', name: 'Tạ Đức Anh', grp: 'kha' }, { id: 'b4', name: 'Cao Thuỳ Dương', grp: 'kha' },
    { id: 'b5', name: 'Phan Nhật Nam', grp: 'kha' }, { id: 'b6', name: 'Hồ Khánh Vy', grp: 'gioi' },
    { id: 'b7', name: 'Lâm Tuệ Nhi', grp: 'gioi' }, { id: 'b8', name: 'Võ Minh Khôi', grp: 'gioi' },
  ],
  '2c': [
    { id: 'c1', name: 'Đoàn Bảo Ngọc', grp: 'yeu' }, { id: 'c2', name: 'Tô Gia Bảo', grp: 'yeu' },
    { id: 'c3', name: 'Hà Minh Thư', grp: 'kha' }, { id: 'c4', name: 'Chu Đức Duy', grp: 'kha' },
    { id: 'c5', name: 'Lương Hà Vi', grp: 'gioi' }, { id: 'c6', name: 'Nguyễn Tuấn Anh', grp: 'gioi' },
  ],
};

export function nowStr() {
  const d = new Date();
  const p = (n) => ('0' + n).slice(-2);
  return `${p(d.getDate())}/${p(d.getMonth() + 1)} ${p(d.getHours())}:${p(d.getMinutes())}`;
}
