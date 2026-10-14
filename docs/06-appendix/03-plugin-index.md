# 插件资源索引

> 本文件由 `php tools/gen_component_docs.php` 自动生成（源自 `Assets::PLUGINS`）。
> 组件在 `assets()` 中返回插件名，由 `Assets::plugin()` 解析依赖并去重加载。
> 路径均相对于 `resources/assets/`。

| 插件名 | CSS | JS | 依赖 | 被以下组件引用 |
|---|---|---|---|---|
| `jquery` | — | 1 个 | — | — |
| `moment` | — | 1 个 | — | — |
| `lucide` | — | 1 个 | — | `icon` |
| `datatables` | 4 个 | 13 个 | — | `dataTable` |
| `datatables-pdf` | — | 2 个 | `datatables` | `dataTable` |
| `apexcharts` | — | 1 个 | — | `apexChart` |
| `apextree` | — | 1 个 | — | `apexTree` |
| `apexsankey` | — | 4 个 | — | `apexSankey` |
| `qrcode` | — | 1 个 | — | `dataTable` |
| `echarts` | — | 1 个 | — | `echart` `metricCard` |
| `svgdotjs` | — | 1 个 | — | — |
| `jsvectormap` | 1 个 | 1 个 | — | — |
| `jsvectormap-world` | — | 2 个 | `jsvectormap` | `vectorMap` |
| `leaflet` | 1 个 | 1 个 | — | `leafletMap` |
| `choices` | 1 个 | 1 个 | — | `select` |
| `select2` | 1 个 | 1 个 | `jquery` | `dataTable` `select` |
| `daterangepicker` | 1 个 | 1 个 | `jquery` `moment` | `datePicker` `dateRange` |
| `nouislider` | 1 个 | 2 个 | — | `slider` |
| `pickr` | 3 个 | 1 个 | — | `colorPicker` |
| `inputmask` | — | 1 个 | — | `input` `maskedInput` |
| `typeahead` | — | 1 个 | `jquery` `handlebars` | — |
| `handlebars` | — | 1 个 | — | — |
| `tagify` | 1 个 | 1 个 | — | `input` `tags` |
| `quill` | 3 个 | 1 个 | — | `editor` `emailCompose` |
| `summernote` | 1 个 | 1 个 | `jquery` | `editor` |
| `dropzone` | 1 个 | 1 个 | — | `upload` |
| `filepond` | 2 个 | 5 个 | — | `upload` |
| `sweetalert2` | 1 个 | 1 个 | — | `sweetAlert` |
| `sortablejs` | — | 1 个 | — | `kanban` `nestable` |
| `simplebar` | 1 个 | 1 个 | — | `kanban` |
| `masonry` | — | 1 个 | — | `gallery` `lightbox` `masonry` |
| `dragsort` | — | 1 个 | — | — |
| `jstree` | 1 个 | 1 个 | `jquery` | `treeView` |
| `muuri` | — | 2 个 | — | — |
| `glightbox` | 1 个 | 1 个 | — | `gallery` `lightbox` |
| `clipboard` | — | 1 个 | — | `clipboard` |
| `tourguide` | 1 个 | 1 个 | — | `tour` |
| `ladda` | 1 个 | 2 个 | — | `button` `loadingButton` |
| `fullcalendar` | — | 1 个 | — | `calendar` |
| `animate` | 1 个 | — | — | `animate` |
| `spinkit` | 1 个 | — | — | `spinner` |
| `pdfjs` | — | 1 个 | — | `pdfViewer` |
| `tinycon` | — | 1 个 | — | `tinycon` |
| `diff` | — | 1 个 | — | `textDiff` |
| `diy` | 1 个 | 1 个 | `sortablejs` | `diyLayoutPage` |

## 明细

### `jquery`

- **JS**：
  - `plugins/jquery/jquery.min.js`

### `moment`

- **JS**：
  - `plugins/moment/moment.min.js`

### `lucide`

- **JS**：
  - `plugins/lucide/lucide.min.js`

### `datatables`

- **CSS**：
  - `plugins/datatables/buttons.bootstrap5.min.css`
  - `plugins/datatables/responsive.bootstrap5.min.css`
  - `plugins/datatables/fixedHeader.bootstrap5.min.css`
  - `plugins/datatables/select.bootstrap5.min.css`
- **JS**：
  - `plugins/datatables/dataTables.min.js`
  - `plugins/datatables/dataTables.bootstrap5.min.js`
  - `plugins/datatables/dataTables.buttons.min.js`
  - `plugins/datatables/buttons.bootstrap5.min.js`
  - `plugins/datatables/jszip.min.js`
  - `plugins/datatables/buttons.html5.min.js`
  - `plugins/datatables/buttons.print.min.js`
  - `plugins/datatables/dataTables.responsive.min.js`
  - `plugins/datatables/responsive.bootstrap5.min.js`
  - `plugins/datatables/dataTables.fixedHeader.min.js`
  - `plugins/datatables/fixedHeader.bootstrap5.min.js`
  - `plugins/datatables/dataTables.select.min.js`
  - `plugins/datatables/select.bootstrap5.min.js`

### `datatables-pdf`

- **依赖**：`datatables`
- **JS**：
  - `plugins/datatables/pdfmake.min.js`
  - `plugins/datatables/vfs_fonts.js`

### `apexcharts`

- **JS**：
  - `plugins/apexcharts/apexcharts.min.js`

### `apextree`

- **JS**：
  - `plugins/apextree/apextree.min.js`

### `apexsankey`

- **JS**：
  - `js/plugins/apexsankey/svg-guard-pre.js`
  - `plugins/svgdotjs/svg.min.js`
  - `js/plugins/apexsankey/apexsankey.min.js`
  - `js/plugins/apexsankey/svg-guard-post.js`

### `qrcode`

- **JS**：
  - `js/plugins/qrcode/qrcode.min.js`

### `echarts`

- **JS**：
  - `plugins/echarts/echarts.min.js`

### `svgdotjs`

- **JS**：
  - `plugins/svgdotjs/svg.min.js`

### `jsvectormap`

- **CSS**：
  - `plugins/jsvectormap/jsvectormap.min.css`
- **JS**：
  - `plugins/jsvectormap/jsvectormap.min.js`

### `jsvectormap-world`

- **依赖**：`jsvectormap`
- **JS**：
  - `plugins/jsvectormap/world.js`
  - `plugins/jsvectormap/world-merc.js`

### `leaflet`

- **CSS**：
  - `plugins/leaflet/leaflet.css`
- **JS**：
  - `plugins/leaflet/leaflet.js`

### `choices`

- **CSS**：
  - `plugins/choices/choices.min.css`
- **JS**：
  - `plugins/choices/choices.min.js`

### `select2`

- **依赖**：`jquery`
- **CSS**：
  - `plugins/select2/select2.min.css`
- **JS**：
  - `plugins/select2/select2.min.js`

### `daterangepicker`

- **依赖**：`jquery`、`moment`
- **CSS**：
  - `plugins/daterangepicker/daterangepicker.css`
- **JS**：
  - `plugins/daterangepicker/daterangepicker.js`

### `nouislider`

- **CSS**：
  - `plugins/nouislider/nouislider.min.css`
- **JS**：
  - `plugins/nouislider/nouislider.min.js`
  - `plugins/wnumb/wNumb.min.js`

### `pickr`

- **CSS**：
  - `plugins/pickr/classic.min.css`
  - `plugins/pickr/monolith.min.css`
  - `plugins/pickr/nano.min.css`
- **JS**：
  - `plugins/pickr/pickr.min.js`

### `inputmask`

- **JS**：
  - `plugins/inputmask/inputmask.min.js`

### `typeahead`

- **依赖**：`jquery`、`handlebars`
- **JS**：
  - `plugins/typeahead/typeahead.bundle.min.js`

### `handlebars`

- **JS**：
  - `plugins/handlebars/handlebars.min.js`

### `tagify`

- **CSS**：
  - `plugins/tagify/tagify.css`
- **JS**：
  - `js/plugins/tagify/tagify.js`

### `quill`

- **CSS**：
  - `plugins/quill/quill.core.css`
  - `plugins/quill/quill.snow.css`
  - `plugins/quill/quill.bubble.css`
- **JS**：
  - `plugins/quill/quill.js`

### `summernote`

- **依赖**：`jquery`
- **CSS**：
  - `plugins/summernote/summernote-bs5.min.css`
- **JS**：
  - `plugins/summernote/summernote-bs5.min.js`

### `dropzone`

- **CSS**：
  - `plugins/dropzone/dropzone.css`
- **JS**：
  - `plugins/dropzone/dropzone-min.js`

### `filepond`

- **CSS**：
  - `plugins/filepond/filepond.min.css`
  - `plugins/filepond/filepond-plugin-image-preview.min.css`
- **JS**：
  - `plugins/filepond/filepond-plugin-file-encode.min.js`
  - `plugins/filepond/filepond-plugin-file-validate-size.min.js`
  - `plugins/filepond/filepond-plugin-image-exif-orientation.min.js`
  - `plugins/filepond/filepond-plugin-image-preview.min.js`
  - `plugins/filepond/filepond.min.js`

### `sweetalert2`

- **CSS**：
  - `plugins/sweetalert2/sweetalert2.min.css`
- **JS**：
  - `plugins/sweetalert2/sweetalert2.min.js`

### `sortablejs`

- **JS**：
  - `plugins/sortablejs/Sortable.min.js`

### `simplebar`

- **CSS**：
  - `plugins/simplebar/simplebar.min.css`
- **JS**：
  - `plugins/simplebar/simplebar.min.js`

### `masonry`

- **JS**：
  - `plugins/masonry/masonry.pkgd.min.js`

### `dragsort`

- **JS**：
  - `js/plugins/dragsort/dragsort.js`

### `jstree`

- **依赖**：`jquery`
- **CSS**：
  - `plugins/jstree/style.min.css`
- **JS**：
  - `plugins/jstree/jstree.min.js`

### `muuri`

- **JS**：
  - `plugins/web-animations/web-animations.min.js`
  - `plugins/muuri/muuri.min.js`

### `glightbox`

- **CSS**：
  - `plugins/glightbox/glightbox.min.css`
- **JS**：
  - `plugins/glightbox/glightbox.min.js`

### `clipboard`

- **JS**：
  - `plugins/clipboard/clipboard.min.js`

### `tourguide`

- **CSS**：
  - `plugins/tourguidejs/tour.min.css`
- **JS**：
  - `plugins/tourguidejs/tour.js`

### `ladda`

- **CSS**：
  - `plugins/ladda/ladda.min.css`
- **JS**：
  - `plugins/ladda/spin.min.js`
  - `plugins/ladda/ladda.min.js`

### `fullcalendar`

- **JS**：
  - `plugins/fullcalendar/index.global.min.js`

### `animate`

- **CSS**：
  - `plugins/animate/animate.min.css`

### `spinkit`

- **CSS**：
  - `plugins/spinkit/spinkit.min.css`

### `pdfjs`

- **JS**：
  - `plugins/pdfjs/pdf.min.js`

### `tinycon`

- **JS**：
  - `plugins/tinycon/tinycon.min.js`

### `diff`

- **JS**：
  - `plugins/diff/diff.min.js`

### `diy`

- **依赖**：`sortablejs`
- **CSS**：
  - `css/xfadmin-diy.css`
- **JS**：
  - `js/xfadmin-diy.js`

