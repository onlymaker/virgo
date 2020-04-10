function pagination(uri, pageNo, pageCount) {
    pageNo = Number(pageNo);
    pageCount = Number(pageCount);

    // pager range to display, i.e. 1 2 3 4 5
    let range = 5;

    let left = Math.floor((pageNo % range ? pageNo : pageNo - 1) / range) * range + 1;
    let right = Math.min(left + range - 1, pageCount);

    let html = "";
    if (pageNo > 1) {
        html += '<li class="page-item"><a class="page-link" href="' + uri + (pageNo - 1) + '">Prev</a></li>';
    } else {
        html += '<li class="page-item disabled"><a class="page-link">Prev</a></li>';
    }
    if (left < right) {
        for (let i = left; i <= right; i ++) {
            if (i === pageNo) {
                html += '<li class="page-item active"><a class="page-link" href="javascript:void(0)">' + i + '</a></li>';
            } else {
                html += '<li class="page-item"><a class="page-link" href="' + uri + i + '">' + i + '</a></li>';
            }
        }
    } else {
        html += '<li class="page-item active"><a class="page-link" href="javascript:void(0)">' + pageNo + '</a></li>';
    }
    if (pageNo < pageCount) {
        html += '<li class="page-item"><a class="page-link" href="' + uri + (pageNo + 1) + '">Next</a></li>';
    } else {
        html += '<li class="page-item disabled"><a class="page-link">Next</a></li>';
    }
    return html;
}
