<div class="sidebar-wrapper sidebar-theme">

    <nav id="sidebar">
        <div class="shadow-bottom"></div>
        <ul class="list-unstyled menu-categories" id="accordionExample">


            <li class="menu active">
                <a  href="{{ route("dashboard") }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span>Dashboard</span>
                    </div>
                </a>
            </li>


            <li class="menu">
                <a  href="{{ route("bankbooks.index") }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M335.9 84.2C326.1 78.6 314 78.6 304.1 84.2L80.1 212.2C67.5 219.4 61.3 234.2 65 248.2C68.7 262.2 81.5 272 96 272L128 272L128 480L128 480L76.8 518.4C68.7 524.4 64 533.9 64 544C64 561.7 78.3 576 96 576L544 576C561.7 576 576 561.7 576 544C576 533.9 571.3 524.4 563.2 518.4L512 480L512 272L544 272C558.5 272 571.2 262.2 574.9 248.2C578.6 234.2 572.4 219.4 559.8 212.2L335.8 84.2zM464 272L464 480L400 480L400 272L464 272zM352 272L352 480L288 480L288 272L352 272zM240 272L240 480L176 480L176 272L240 272zM320 160C337.7 160 352 174.3 352 192C352 209.7 337.7 224 320 224C302.3 224 288 209.7 288 192C288 174.3 302.3 160 320 160z"/></svg>
                        <span>Bank Book</span>
                    </div>
                </a>
            </li>

            <li class="menu">
                <a href="#Export" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M404 207.9L204.7 104.2C196.7 100.1 187.4 99.4 179 102.5L137.9 117.5C127.6 121.2 124.1 133.9 130.8 142.5L232.3 270.4L132.1 306.8L72 270.2C65.8 266.4 58.2 265.7 51.3 268.1L35 274.1C25.6 277.5 21.6 288.6 26.7 297.2L80.3 389C95.9 415.7 128.4 427.4 157.4 416.8L170.3 412.1L170.3 412.1L568.7 267.1C597.8 256.5 612.7 224.4 602.2 195.3C591.7 166.2 559.5 151.3 530.4 161.8L404 207.9zM64.2 512C46.5 512 32.2 526.3 32.2 544C32.2 561.7 46.5 576 64.2 576L576.2 576C593.9 576 608.2 561.7 608.2 544C608.2 526.3 593.9 512 576.2 512L64.2 512z"/></svg>
                        <span>Export</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled" id="Export" data-bs-parent="#accordionExample">
                    <li>
                        <a href="{{ route('export-bills.create') }}"> Export Bill </a>
                    </li>
                    <li>
                        <a href="{{ route('export-bills.index') }}"> Export Summary </a>
                    </li>

                </ul>
            </li>


            <li class="menu">
                <a href="#Import" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M418.6 257.1L297.9 67.7C293.1 60.1 285.3 54.9 276.5 53.4L233.4 45.8C222.6 43.9 213.2 53.1 214.8 63.9L238.8 225.5L133.8 207L100 145.2C96.5 138.7 90.2 134.2 83.1 133L66 130C56.2 128.3 47.2 135.9 47.2 145.8L47.8 252.1C48 283 70.2 309.4 100.7 314.8L114.2 317.2L114.2 317.2L531.8 390.8C562.3 396.2 591.3 375.8 596.7 345.4C602.1 315 581.7 285.9 551.3 280.5L418.6 257.1zM256 448C273.7 448 288 433.7 288 416C288 398.3 273.7 384 256 384C238.3 384 224 398.3 224 416C224 433.7 238.3 448 256 448zM387.2 432.7C387.2 415 372.9 400.7 355.2 400.7C337.5 400.7 323.2 415 323.2 432.7C323.2 450.4 337.5 464.7 355.2 464.7C372.9 464.7 387.2 450.4 387.2 432.7zM64 512C46.3 512 32 526.3 32 544C32 561.7 46.3 576 64 576L576 576C593.7 576 608 561.7 608 544C608 526.3 593.7 512 576 512L64 512z"/></svg>
                        <span>Import</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled" id="Import" data-bs-parent="#accordionExample">
                    <li>
                        <a href="{{ route("import-bills.create") }}"> Import Bill </a>
                    </li>
                    <li>
                        <a href="{{ route("import-bills.index") }}"> Import Summary </a>
                    </li>

                </ul>
            </li>

            <li class="menu">
                <a href="#Expense" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M544 72C544 58.7 533.3 48 520 48L418.2 48C404.9 48 394.2 58.7 394.2 72C394.2 85.3 404.9 96 418.2 96L462.1 96L350.8 207.3L255.7 125.8C246.7 118.1 233.5 118.1 224.5 125.8L112.5 221.8C102.4 230.4 101.3 245.6 109.9 255.6C118.5 265.6 133.7 266.8 143.7 258.2L240.1 175.6L336.5 258.2C346 266.4 360.2 265.8 369.1 256.9L496.1 129.9L496.1 173.8C496.1 187.1 506.8 197.8 520.1 197.8C533.4 197.8 544.1 187.1 544.1 173.8L544 72zM112 320C85.5 320 64 341.5 64 368L64 528C64 554.5 85.5 576 112 576L528 576C554.5 576 576 554.5 576 528L576 368C576 341.5 554.5 320 528 320L112 320zM159.3 376C155.9 396.1 140.1 412 119.9 415.4C115.5 416.1 111.9 412.5 111.9 408.1L111.9 376.1C111.9 371.7 115.5 368.1 119.9 368.1L151.9 368.1C156.3 368.1 160 371.7 159.2 376.1zM159.3 520.1C160 524.5 156.4 528.1 152 528.1L120 528.1C115.6 528.1 112 524.5 112 520.1L112 488.1C112 483.7 115.6 480 120 480.8C140.1 484.2 156 500 159.4 520.2zM520 480.7C524.4 480 528 483.6 528 488L528 520C528 524.4 524.4 528 520 528L488 528C483.6 528 479.9 524.4 480.7 520C484.1 499.9 499.9 484 520.1 480.6zM480.7 376C480 371.6 483.6 368 488 368L520 368C524.4 368 528 371.6 528 376L528 408C528 412.4 524.4 416.1 520 415.3C499.9 411.9 484 396.1 480.6 375.9zM256 448C256 412.7 284.7 384 320 384C355.3 384 384 412.7 384 448C384 483.3 355.3 512 320 512C284.7 512 256 483.3 256 448z"/></svg>
                        <span>Expense</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled" id="Expense" data-bs-parent="#accordionExample">
                    <li>
                        <a href="{{ route("expenses.index") }}"> Expense List </a>
                    </li>
                    <li>
                        <a href="{{ route("categories.index") }}"> Expense Category </a>
                    </li>

                </ul>
            </li>

            <li class="menu">
                <a  href="{{ route('employees.index') }}" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M302.3-12.6c-9-4.5-19.6-4.5-28.6 0l-256 128C1.9 123.3-4.5 142.5 3.4 158.3s27.1 22.2 42.9 14.3L288 51.8 529.7 172.6c15.8 7.9 35 1.5 42.9-14.3s1.5-35-14.3-42.9l-256-128zM288 272a56 56 0 1 0 0-112 56 56 0 1 0 0 112zm0 48c-53 0-96 43-96 96l0 32c0 17.7 14.3 32 32 32l128 0c17.7 0 32-14.3 32-32l0-32c0-53-43-96-96-96zM160 256a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zm352 0a48 48 0 1 0 -96 0 48 48 0 1 0 96 0zM112 336c-44.2 0-80 35.8-80 80l0 33.1c0 17 13.8 30.9 30.9 30.9l87.8 0c-4.3-9.8-6.7-20.6-6.7-32l0-48c0-18.4 3.5-36 9.8-52.2-12.2-7.5-26.5-11.8-41.8-11.8zM425.4 480l87.8 0c17 0 30.9-13.8 30.9-30.9l0-33.1c0-44.2-35.8-80-80-80-15.3 0-29.6 4.3-41.8 11.8 6.3 16.2 9.8 33.8 9.8 52.2l0 48c0 11.4-2.4 22.2-6.7 32z"/></svg>
                        <span>Employee</span>
                    </div>
                </a>
            </li>


            <li class="menu">
                <a href="#EmployeeCash" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M96 128C96 92.7 124.7 64 160 64C195.3 64 224 92.7 224 128C224 163.3 195.3 192 160 192C124.7 192 96 163.3 96 128zM64 288C64 252.7 92.7 224 128 224L192 224C195.2 224 198.4 224.2 201.5 224.7L157.1 269.1C129 297.2 129 342.8 157.1 370.9L213.1 426.9C216.5 430.3 220.1 433.3 224 435.9L224 528C224 554.5 202.5 576 176 576L144 576C117.5 576 96 554.5 96 528L96 407.4C76.9 396.4 64 375.7 64 352L64 288zM416 128C416 92.7 444.7 64 480 64C515.3 64 544 92.7 544 128C544 163.3 515.3 192 480 192C444.7 192 416 163.3 416 128zM482.9 269.1L438.5 224.7C441.6 224.2 444.8 224 448 224L512 224C547.3 224 576 252.7 576 288L576 352C576 375.7 563.1 396.4 544 407.4L544 528C544 554.5 522.5 576 496 576L464 576C437.5 576 416 554.5 416 528L416 435.9C419.9 433.3 423.5 430.3 426.9 426.9L482.9 370.9C511 342.8 511 297.2 482.9 269.1zM366.8 241.8C375.8 238.1 386.1 240.1 393 247L449 303C458.4 312.4 458.4 327.6 449 336.9L393 392.9C386.1 399.8 375.8 401.8 366.8 398.1C357.8 394.4 352 385.7 352 376L352 352L288 352L288 376C288 385.7 282.2 394.5 273.2 398.2C264.2 401.9 253.9 399.9 247 393L191 337C181.6 327.6 181.6 312.4 191 303.1L247 247.1C253.9 240.2 264.2 238.2 273.2 241.9C282.2 245.6 288 254.3 288 264L288 288L352 288L352 264C352 254.3 357.8 245.5 366.8 241.8z"/></svg>
                        <span>Employee Cash</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled" id="EmployeeCash" data-bs-parent="#accordionExample">
                    <li>
                        <a href="{{ route("transactions.import") }}"> Import  Cash </a>
                    </li>
                    <li>
                        <a href="{{ route("transactions.export") }}"> Export Cash</a>
                    </li>

                </ul>
            </li>


            <li class="menu">
                <a href="#Report" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M256 144C256 117.5 277.5 96 304 96L336 96C362.5 96 384 117.5 384 144L384 496C384 522.5 362.5 544 336 544L304 544C277.5 544 256 522.5 256 496L256 144zM64 336C64 309.5 85.5 288 112 288L144 288C170.5 288 192 309.5 192 336L192 496C192 522.5 170.5 544 144 544L112 544C85.5 544 64 522.5 64 496L64 336zM496 160L528 160C554.5 160 576 181.5 576 208L576 496C576 522.5 554.5 544 528 544L496 544C469.5 544 448 522.5 448 496L448 208C448 181.5 469.5 160 496 160z"/></svg>
                        <span>Report</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled" id="Report" data-bs-parent="#accordionExample">
                    <li>
                        <a href="{{ route('bankbook.report') }}"> Bank Book Report </a>
                    </li>
                    <li>
                        <a href="{{ route('expense.report') }}"> Expense Report </a>
                    </li>
                    <li>
                        <a href="{{ route('import.bill.report') }}"> Import Bill Report </a>
                    </li>
                    <li>
                        <a href="{{ route('import.bill.summary.report') }}"> Import Bill Statement </a>
                    </li>
                    <li>
                        <a href="{{ route('export.bill.report') }}"> Export Bill Report </a>
                    </li>

                </ul>
            </li>


            <li class="menu">
                <a href="#Settings" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle">
                    <div class="">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><!--!Font Awesome Free v7.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#383838" d="M415.9 274.5C428.1 271.2 440.9 277 446.4 288.3L465 325.9C475.3 327.3 485.4 330.1 494.9 334L529.9 310.7C540.4 303.7 554.3 305.1 563.2 314L582.4 333.2C591.3 342.1 592.7 356.1 585.7 366.5L562.4 401.4C564.3 406.1 566 411 567.4 416.1C568.8 421.2 569.7 426.2 570.4 431.3L608.1 449.9C619.4 455.5 625.2 468.3 621.9 480.4L614.9 506.6C611.6 518.7 600.3 526.9 587.7 526.1L545.7 523.4C539.4 531.5 532.1 539 523.8 545.4L526.5 587.3C527.3 599.9 519.1 611.3 507 614.5L480.8 621.5C468.6 624.8 455.9 619 450.3 607.7L431.7 570.1C421.4 568.7 411.3 565.9 401.8 562L366.8 585.3C356.3 592.3 342.4 590.9 333.5 582L314.3 562.8C305.4 553.9 304 540 311 529.5L334.3 494.5C332.4 489.8 330.7 484.9 329.3 479.8C327.9 474.7 327 469.6 326.3 464.6L288.6 446C277.3 440.4 271.6 427.6 274.8 415.5L281.8 389.3C285.1 377.2 296.4 369 309 369.8L350.9 372.5C357.2 364.4 364.5 356.9 372.8 350.5L370.1 308.7C369.3 296.1 377.5 284.7 389.6 281.5L415.8 274.5zM448.4 404C424.1 404 404.4 423.7 404.5 448.1C404.5 472.4 424.2 492 448.5 492C472.8 492 492.5 472.3 492.5 448C492.4 423.6 472.7 404 448.4 404zM224.9 18.5L251.1 25.5C263.2 28.8 271.4 40.2 270.6 52.7L267.9 94.5C276.2 100.9 283.5 108.3 289.8 116.5L331.8 113.8C344.3 113 355.7 121.2 359 133.3L366 159.5C369.2 171.6 363.5 184.4 352.2 190L314.5 208.6C313.8 213.7 312.8 218.8 311.5 223.8C310.2 228.8 308.4 233.8 306.5 238.5L329.8 273.5C336.8 284 335.4 297.9 326.5 306.8L307.3 326C298.4 334.9 284.5 336.3 274 329.3L239 306C229.5 309.9 219.4 312.7 209.1 314.1L190.5 351.7C184.9 363 172.1 368.7 160 365.5L133.8 358.5C121.6 355.2 113.5 343.8 114.3 331.3L117 289.4C108.7 283 101.4 275.6 95.1 267.4L53.1 270.1C40.6 270.9 29.2 262.7 25.9 250.6L18.9 224.4C15.7 212.3 21.4 199.5 32.7 193.9L70.4 175.3C71.1 170.2 72.1 165.2 73.4 160.1C74.8 155 76.4 150.1 78.4 145.4L55.1 110.5C48.1 100 49.5 86.1 58.4 77.2L77.6 58C86.5 49.1 100.4 47.7 110.9 54.7L145.9 78C155.4 74.1 165.5 71.3 175.8 69.9L194.4 32.3C200 21 212.7 15.3 224.9 18.5zM192.4 148C168.1 148 148.4 167.7 148.4 192C148.4 216.3 168.1 236 192.4 236C216.7 236 236.4 216.3 236.4 192C236.4 167.7 216.7 148 192.4 148z"/></svg>
                        <span>Settings</span>
                    </div>
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </div>
                </a>
                <ul class="collapse submenu list-unstyled" id="Settings" data-bs-parent="#accordionExample">
                    <li>
                        <a href="{{ route('accounts.index') }}"> Account Settings </a>
                    </li>
                    <li>
                        <a href="user-account-settings.php"> Profile Settings </a>
                    </li>

                </ul>
            </li>
        </ul>

    </nav>

</div>
