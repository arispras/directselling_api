<style type='text/css'>
	/* ROOT */
	body {
		background-color: white;
		font-family: Helvetica, arial, sans-serif;
	}

	body * {
		
		border-spacing: 0;
	}

	h1.title {
		margin-bottom: 0px;
		line-height: 30px;
		text-align: center;
	}

	h3.title {
		margin-bottom: 0px;
		line-height: 30px;
	}

	h4.title {
		margin-bottom: 0px;
		text-align: center;
	}

	hr.top {
		border: none;
		border-bottom: 2px solid #333;
		margin-bottom: 10px;
		margin-top: 10px;
	}

	/* TABLE */
	table {
		width: 100%;
	}
	table.param{
		line-height: 1.6;
	}

	table.border {
		border: 0.5px solid rgba(0, 0, 0, 0.4);
	}

	.table-bg th,
	.table-bg td {
		font-size: 0.8em;
	}

	.table-bg th {
		color: black;
		/* background: linear-gradient(to right, #9dc9fa, #9dc9fa); */
		background: rgba(50, 50, 50, 0.1);
		text-align: center !important;
		font-weight: bolder;
		text-transform: uppercase;
	}

	.table-bg th,
	.table-bg td {
		border: 0.8px solid rgba(0, 0, 0, 0.4);
		padding: 5px 8px;
	}

	.tab-expand th,
	.tab-expand td {
		padding: 5px 10px;
		width: auto;
	}

	.tab-expand.border,
	.tab-expand.border th,
	.tab-expand.border td {
		border: 0.8px solid rgba(0, 0, 0, 0.2);
	}


	/* HELPER */
	[d],
	[d] * {
		border: 1px solid red;
	}

	[dd] {
		border: 1px solid blue;
	}

	[center] {
		text-align: center !important;
	}

	[left] {
		text-align: left !important;
	}

	[right] {
		text-align: right !important;
	}

	[bold] {
		font-weight: bold !important;
	}


	/* KOP PRINT */
	.kop-print {
		width: 1000px;
		margin: auto;
	}

	.kop-print img {
		float: left;
		height: 60px;
		margin-right: 20px;
	}

	.kop-print .kop-info {
		font-size: 15px;
	}

	.kop-print .kop-nama {
		font-size: 25px;
		font-weight: bold;
		line-height: 35px;
	}

	.kop-print-hr {
		border-color: rgba(0, 0, 0, 0, 1);
		margin-bottom: 0px;
	}


	.pagenum:before {
		/* content: counter(page); */
		content: counter(page) ' of 'counter(pages);
	}

	page {
		background: white;
		display: block;
		margin: 0 auto;
		margin-bottom: 0.5cm;
		box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
	}

	page[size="A4"] {
		width: 21cm;
		min-height: 29.7cm;
	}

	page[size="A4"][layout="portrait"] {
		width: 29.7cm;
		height: 21cm;
	}

	page[size="A3"] {
		width: 29.7cm;
		height: 42cm;
	}

	page[size="A3"][layout="portrait"] {
		width: 42cm;
		height: 29.7cm;
	}

	page[size="A5"] {
		width: 14.8cm;
		height: 21cm;
	}

	page[size="A5"][layout="portrait"] {
		width: 21cm;
		height: 14.8cm;
	}

	#pageCounter {
		counter-reset: pageTotal;
	}

	#pageCounter page {
		counter-increment: pageTotal;
	}

	#pageNumbers {
	counter-reset: currentPage;
	/* width:500px;
	background:#000;
	margin:auto;
	color:#fff;
	padding:30px */

	}

	#pageNumbers div:before {
		counter-increment: currentPage;
		content: counter(currentPage) " / ";
	}

	#pageNumbers div:after {
		content: counter(pageTotal);
	}

	.page-number {
		/* display:block; */
		font-size: 20px;
		/* margin:20px;
		text-align:center;
		border-bottom:1px solid #555 */
	}

	.page-number:after {
		counter-increment: page;
	}


	@page {
		/* margin-top: 149px;
        margin-left: 2px;
        margin-bottom: 40px;
        margin-right: 2px;
        size: landscape; */
		counter-increment: page;

		@bottom-right {
			padding-right: 20px;
			/* content: "Page " counter(page); */
			content: counter(page) ' of 'counter(pages);
		}

	}

	/* @page {
  @bottom-left {
    content: counter(page) ' of ' counter(pages);
  }
} */
</style>
