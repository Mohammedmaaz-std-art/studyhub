<?php
/**
 * StudyHub Question Bank
 * Structure: course → subject_slug → array of questions
 * Each question: question, options (a/b/c/d), correct (a/b/c/d)
 * 10 questions per subject minimum.
 * Subject slugs must match lowercase subject names for matching.
 */

$QUESTION_BANK = [

  // ══════════════════════════════════════════════════════
  // BCA
  // ══════════════════════════════════════════════════════
  'BCA' => [

    'dbms' => [
      ['question' => 'What does DBMS stand for?',
       'options'  => ['a'=>'Database Management System','b'=>'Data Backup Management System','c'=>'Database Monitoring Service','d'=>'Data Based Modelling System'],
       'correct'  => 'a'],
      ['question' => 'Which of the following is NOT a type of database model?',
       'options'  => ['a'=>'Relational','b'=>'Hierarchical','c'=>'Network','d'=>'Sequential'],
       'correct'  => 'd'],
      ['question' => 'A primary key must be:',
       'options'  => ['a'=>'Nullable and unique','b'=>'Non-null and unique','c'=>'Unique but nullable','d'=>'Non-null but can repeat'],
       'correct'  => 'b'],
      ['question' => 'Which SQL command is used to remove a table completely?',
       'options'  => ['a'=>'DELETE','b'=>'REMOVE','c'=>'DROP','d'=>'TRUNCATE'],
       'correct'  => 'c'],
      ['question' => 'A foreign key in a table references the __ of another table.',
       'options'  => ['a'=>'Foreign key','b'=>'Primary key','c'=>'Candidate key','d'=>'Composite key'],
       'correct'  => 'b'],
      ['question' => 'Which normal form eliminates partial dependencies?',
       'options'  => ['a'=>'1NF','b'=>'2NF','c'=>'3NF','d'=>'BCNF'],
       'correct'  => 'b'],
      ['question' => 'The SQL command to retrieve data from a table is:',
       'options'  => ['a'=>'GET','b'=>'FETCH','c'=>'SELECT','d'=>'RETRIEVE'],
       'correct'  => 'c'],
      ['question' => 'Which JOIN returns all rows from both tables, with NULLs where no match exists?',
       'options'  => ['a'=>'INNER JOIN','b'=>'LEFT JOIN','c'=>'RIGHT JOIN','d'=>'FULL OUTER JOIN'],
       'correct'  => 'd'],
      ['question' => 'ACID in database transactions stands for:',
       'options'  => ['a'=>'Atomicity, Consistency, Isolation, Durability','b'=>'Access, Control, Integrity, Data','c'=>'Atomicity, Concurrency, Integrity, Durability','d'=>'Access, Consistency, Isolation, Distribution'],
       'correct'  => 'a'],
      ['question' => 'Which of the following is a DDL command?',
       'options'  => ['a'=>'SELECT','b'=>'INSERT','c'=>'CREATE','d'=>'UPDATE'],
       'correct'  => 'c'],
    ],

    'data structures' => [
      ['question' => 'Which data structure follows LIFO order?',
       'options'  => ['a'=>'Queue','b'=>'Stack','c'=>'Array','d'=>'Tree'],
       'correct'  => 'b'],
      ['question' => 'The time complexity of binary search is:',
       'options'  => ['a'=>'O(n)','b'=>'O(n²)','c'=>'O(log n)','d'=>'O(1)'],
       'correct'  => 'c'],
      ['question' => 'Which data structure uses FIFO ordering?',
       'options'  => ['a'=>'Stack','b'=>'Tree','c'=>'Graph','d'=>'Queue'],
       'correct'  => 'd'],
      ['question' => 'A linked list node contains:',
       'options'  => ['a'=>'Data only','b'=>'Pointer only','c'=>'Data and a pointer to the next node','d'=>'Index and data'],
       'correct'  => 'c'],
      ['question' => 'Which traversal visits root, then left, then right?',
       'options'  => ['a'=>'Inorder','b'=>'Postorder','c'=>'Preorder','d'=>'Level-order'],
       'correct'  => 'c'],
      ['question' => 'The worst-case time complexity of bubble sort is:',
       'options'  => ['a'=>'O(n log n)','b'=>'O(n)','c'=>'O(n²)','d'=>'O(log n)'],
       'correct'  => 'c'],
      ['question' => 'In a binary search tree, the left child is always:',
       'options'  => ['a'=>'Greater than the parent','b'=>'Less than the parent','c'=>'Equal to the parent','d'=>'Random'],
       'correct'  => 'b'],
      ['question' => 'Which algorithm uses a stack to traverse graphs?',
       'options'  => ['a'=>'BFS','b'=>'Dijkstra','c'=>'DFS','d'=>'Prim'],
       'correct'  => 'c'],
      ['question' => 'An array of size n has indices from:',
       'options'  => ['a'=>'1 to n','b'=>'0 to n','c'=>'0 to n-1','d'=>'1 to n-1'],
       'correct'  => 'c'],
      ['question' => 'Which data structure is used to implement recursion?',
       'options'  => ['a'=>'Queue','b'=>'Stack','c'=>'Heap','d'=>'Graph'],
       'correct'  => 'b'],
    ],

    'web development' => [
      ['question' => 'What does HTML stand for?',
       'options'  => ['a'=>'HyperText Markup Language','b'=>'HighText Machine Language','c'=>'HyperText Machine Language','d'=>'HighText Markup Language'],
       'correct'  => 'a'],
      ['question' => 'Which CSS property controls text size?',
       'options'  => ['a'=>'text-size','b'=>'font-size','c'=>'text-style','d'=>'font-weight'],
       'correct'  => 'b'],
      ['question' => 'In PHP, which superglobal holds form POST data?',
       'options'  => ['a'=>'$_GET','b'=>'$_REQUEST','c'=>'$_POST','d'=>'$_FORM'],
       'correct'  => 'c'],
      ['question' => 'Which SQL function counts the number of rows?',
       'options'  => ['a'=>'SUM()','b'=>'TOTAL()','c'=>'COUNT()','d'=>'NUM()'],
       'correct'  => 'c'],
      ['question' => 'What does CSS stand for?',
       'options'  => ['a'=>'Computer Style Sheets','b'=>'Cascading Style Sheets','c'=>'Creative Style Sheets','d'=>'Colorful Style Sheets'],
       'correct'  => 'b'],
      ['question' => 'Which HTML tag is used to link an external CSS file?',
       'options'  => ['a'=>'<style>','b'=>'<css>','c'=>'<link>','d'=>'<script>'],
       'correct'  => 'c'],
      ['question' => 'JavaScript runs on:',
       'options'  => ['a'=>'The server only','b'=>'The client (browser) only','c'=>'Both client and server','d'=>'The database'],
       'correct'  => 'c'],
      ['question' => 'Which method is used to send an HTTP request in JavaScript?',
       'options'  => ['a'=>'send()','b'=>'request()','c'=>'fetch()','d'=>'get()'],
       'correct'  => 'c'],
      ['question' => 'Which of the following is NOT a valid HTTP method?',
       'options'  => ['a'=>'GET','b'=>'POST','c'=>'SEND','d'=>'DELETE'],
       'correct'  => 'c'],
      ['question' => 'PHP sessions are stored:',
       'options'  => ['a'=>'In the browser cookie','b'=>'On the server','c'=>'In the database','d'=>'In localStorage'],
       'correct'  => 'b'],
    ],

    'operating systems' => [
      ['question' => 'What is a process in an operating system?',
       'options'  => ['a'=>'A program stored on disk','b'=>'A program in execution','c'=>'A file in memory','d'=>'A hardware component'],
       'correct'  => 'b'],
      ['question' => 'Which scheduling algorithm gives the shortest job the CPU first?',
       'options'  => ['a'=>'FCFS','b'=>'Round Robin','c'=>'SJF','d'=>'Priority Scheduling'],
       'correct'  => 'c'],
      ['question' => 'Deadlock occurs when:',
       'options'  => ['a'=>'A process uses too much CPU','b'=>'Processes wait for each other indefinitely','c'=>'Memory is full','d'=>'The CPU is idle'],
       'correct'  => 'b'],
      ['question' => 'Virtual memory allows:',
       'options'  => ['a'=>'Programs to use more memory than physically available','b'=>'Two programs to share the same memory address','c'=>'The CPU to run faster','d'=>'Disk access to be faster than RAM'],
       'correct'  => 'a'],
      ['question' => 'Which of the following is NOT a process state?',
       'options'  => ['a'=>'Running','b'=>'Waiting','c'=>'Compiling','d'=>'Ready'],
       'correct'  => 'c'],
      ['question' => 'A semaphore is used for:',
       'options'  => ['a'=>'Memory allocation','b'=>'Process scheduling','c'=>'Process synchronization','d'=>'File management'],
       'correct'  => 'c'],
      ['question' => 'Thrashing in operating systems refers to:',
       'options'  => ['a'=>'Excessive paging activity that reduces performance','b'=>'A virus attacking the OS','c'=>'CPU running at 100% utilization','d'=>'Too many processes creating files'],
       'correct'  => 'a'],
      ['question' => 'Which page replacement algorithm replaces the page not used for the longest time?',
       'options'  => ['a'=>'FIFO','b'=>'LRU','c'=>'Optimal','d'=>'Clock'],
       'correct'  => 'c'],
      ['question' => 'A thread is:',
       'options'  => ['a'=>'A separate program','b'=>'A lightweight process within a process','c'=>'A CPU core','d'=>'A type of memory'],
       'correct'  => 'b'],
      ['question' => 'Which of the following is a real-time operating system?',
       'options'  => ['a'=>'Windows 10','b'=>'Ubuntu','c'=>'VxWorks','d'=>'macOS'],
       'correct'  => 'c'],
    ],

    'python programming' => [
      ['question' => 'Which of the following is the correct way to define a function in Python?',
       'options'  => ['a'=>'function myFunc():','b'=>'def myFunc():','c'=>'define myFunc():','d'=>'fun myFunc():'],
       'correct'  => 'b'],
      ['question' => 'What is the output of: print(type(5.0))?',
       'options'  => ['a'=>"<class 'int'>",'b'=>"<class 'str'>",'c'=>"<class 'float'>",'d'=>"<class 'double'>"],
       'correct'  => 'c'],
      ['question' => 'Which data structure in Python is ordered and immutable?',
       'options'  => ['a'=>'List','b'=>'Dictionary','c'=>'Set','d'=>'Tuple'],
       'correct'  => 'd'],
      ['question' => 'What does len([1, 2, 3]) return?',
       'options'  => ['a'=>'2','b'=>'3','c'=>'4','d'=>'Error'],
       'correct'  => 'b'],
      ['question' => 'Which keyword is used to handle exceptions in Python?',
       'options'  => ['a'=>'catch','b'=>'handle','c'=>'except','d'=>'error'],
       'correct'  => 'c'],
      ['question' => 'What is the correct file extension for Python files?',
       'options'  => ['a'=>'.py','b'=>'.python','c'=>'.pt','d'=>'.pyt'],
       'correct'  => 'a'],
      ['question' => 'Which of the following is used to inherit a class in Python?',
       'options'  => ['a'=>'class Child extends Parent:','b'=>'class Child inherits Parent:','c'=>'class Child(Parent):','d'=>'class Child:Parent:'],
       'correct'  => 'c'],
      ['question' => 'What does the range(1, 5) function produce?',
       'options'  => ['a'=>'1, 2, 3, 4, 5','b'=>'1, 2, 3, 4','c'=>'0, 1, 2, 3, 4','d'=>'1, 2, 3'],
       'correct'  => 'b'],
      ['question' => 'Which Python module is used for mathematical operations?',
       'options'  => ['a'=>'math','b'=>'calc','c'=>'numbers','d'=>'arithmetic'],
       'correct'  => 'a'],
      ['question' => 'What is the output of: print(10 // 3)?',
       'options'  => ['a'=>'3.33','b'=>'3','c'=>'4','d'=>'3.0'],
       'correct'  => 'b'],
    ],

  ], // end BCA

  // ══════════════════════════════════════════════════════
  // BCom
  // ══════════════════════════════════════════════════════
  'BCom' => [

    'accountancy' => [
      ['question' => 'The accounting equation is:',
       'options'  => ['a'=>'Assets = Liabilities + Capital','b'=>'Assets = Capital - Liabilities','c'=>'Liabilities = Assets + Capital','d'=>'Capital = Assets + Liabilities'],
       'correct'  => 'a'],
      ['question' => 'Which account has a credit balance normally?',
       'options'  => ['a'=>'Cash Account','b'=>'Purchases Account','c'=>'Capital Account','d'=>'Expense Account'],
       'correct'  => 'c'],
      ['question' => 'Depreciation is charged on:',
       'options'  => ['a'=>'Current assets','b'=>'Fixed assets','c'=>'Liquid assets','d'=>'Fictitious assets'],
       'correct'  => 'b'],
      ['question' => 'The trial balance is prepared to:',
       'options'  => ['a'=>'Find profit or loss','b'=>'Check arithmetic accuracy of ledger','c'=>'Prepare cash flow statement','d'=>'Calculate tax'],
       'correct'  => 'b'],
      ['question' => 'Which financial statement shows the financial position of a business?',
       'options'  => ['a'=>'Income Statement','b'=>'Cash Flow Statement','c'=>'Balance Sheet','d'=>'Trial Balance'],
       'correct'  => 'c'],
      ['question' => 'Goodwill is an example of:',
       'options'  => ['a'=>'Current asset','b'=>'Tangible fixed asset','c'=>'Intangible asset','d'=>'Liquid asset'],
       'correct'  => 'c'],
      ['question' => 'Which of the following is a revenue expenditure?',
       'options'  => ['a'=>'Purchase of machinery','b'=>'Repair of machinery','c'=>'Purchase of land','d'=>'Construction of building'],
       'correct'  => 'b'],
      ['question' => 'The concept of going concern assumes:',
       'options'  => ['a'=>'Business will close soon','b'=>'Business will continue indefinitely','c'=>'Assets will be sold','d'=>'Profits will increase'],
       'correct'  => 'b'],
      ['question' => 'Outstanding expenses are shown in the balance sheet as:',
       'options'  => ['a'=>'Asset','b'=>'Revenue','c'=>'Liability','d'=>'Capital'],
       'correct'  => 'c'],
      ['question' => 'Which method of depreciation gives equal charge each year?',
       'options'  => ['a'=>'Written Down Value','b'=>'Straight Line Method','c'=>'Sum of Digits','d'=>'Annuity Method'],
       'correct'  => 'b'],
    ],

    'economics' => [
      ['question' => 'Economics is the study of:',
       'options'  => ['a'=>'Money and banking','b'=>'Scarcity and choice','c'=>'Government policies','d'=>'Stock markets'],
       'correct'  => 'b'],
      ['question' => 'When price increases and demand falls, demand is said to be:',
       'options'  => ['a'=>'Inelastic','b'=>'Elastic','c'=>'Unitary','d'=>'Perfectly elastic'],
       'correct'  => 'b'],
      ['question' => 'GDP stands for:',
       'options'  => ['a'=>'General Domestic Product','b'=>'Gross Domestic Product','c'=>'Global Domestic Production','d'=>'Gross Development Product'],
       'correct'  => 'b'],
      ['question' => 'Inflation refers to:',
       'options'  => ['a'=>'Decrease in money supply','b'=>'Rise in general price level','c'=>'Fall in GDP','d'=>'Decrease in employment'],
       'correct'  => 'b'],
      ['question' => 'A market where one seller controls the entire supply is called:',
       'options'  => ['a'=>'Oligopoly','b'=>'Perfect Competition','c'=>'Monopoly','d'=>'Duopoly'],
       'correct'  => 'c'],
      ['question' => 'The law of diminishing marginal utility states that:',
       'options'  => ['a'=>'Total utility always falls','b'=>'Marginal utility rises with consumption','c'=>'Marginal utility falls as more units are consumed','d'=>'Utility is constant'],
       'correct'  => 'c'],
      ['question' => 'Fiscal policy refers to:',
       'options'  => ['a'=>'Central bank controlling money supply','b'=>'Government using taxation and spending to influence economy','c'=>'Trade policy between countries','d'=>'Exchange rate management'],
       'correct'  => 'b'],
      ['question' => 'Supply curve typically slopes:',
       'options'  => ['a'=>'Downward','b'=>'Horizontal','c'=>'Upward','d'=>'Vertical'],
       'correct'  => 'c'],
      ['question' => 'Which of the following is an example of a public good?',
       'options'  => ['a'=>'A private hospital','b'=>'Street lighting','c'=>'A restaurant meal','d'=>'A cinema ticket'],
       'correct'  => 'b'],
      ['question' => 'The multiplier effect occurs when:',
       'options'  => ['a'=>'Taxes are increased','b'=>'An initial injection of spending leads to a larger final increase in income','c'=>'Interest rates fall','d'=>'Imports exceed exports'],
       'correct'  => 'b'],
    ],

    'business studies' => [
      ['question' => 'Management is best defined as:',
       'options'  => ['a'=>'Doing things alone','b'=>'Getting things done through others','c'=>'Planning only','d'=>'Controlling resources'],
       'correct'  => 'b'],
      ['question' => 'Which function of management involves setting objectives?',
       'options'  => ['a'=>'Organizing','b'=>'Staffing','c'=>'Planning','d'=>'Controlling'],
       'correct'  => 'c'],
      ['question' => 'A sole proprietorship is owned by:',
       'options'  => ['a'=>'Two partners','b'=>'Shareholders','c'=>'One individual','d'=>'The government'],
       'correct'  => 'c'],
      ['question' => 'Marketing mix refers to:',
       'options'  => ['a'=>'Advertising alone','b'=>'Product, Price, Place and Promotion','c'=>'Distribution channels','d'=>'Market research'],
       'correct'  => 'b'],
      ['question' => 'The span of control refers to:',
       'options'  => ['a'=>'Number of products a company sells','b'=>'Number of subordinates a manager directly supervises','c'=>'Range of markets served','d'=>'Budget allocated to a department'],
       'correct'  => 'b'],
      ['question' => 'Break-even point is where:',
       'options'  => ['a'=>'Profit is maximum','b'=>'Total costs equal total revenue','c'=>'Fixed costs are covered','d'=>'Variable costs are zero'],
       'correct'  => 'b'],
      ['question' => 'Which leadership style involves the leader making all decisions?',
       'options'  => ['a'=>'Democratic','b'=>'Laissez-faire','c'=>'Autocratic','d'=>'Transformational'],
       'correct'  => 'c'],
      ['question' => 'Working capital is:',
       'options'  => ['a'=>'Long-term funds','b'=>'Current assets minus current liabilities','c'=>'Fixed assets minus depreciation','d'=>'Total capital employed'],
       'correct'  => 'b'],
      ['question' => 'An entrepreneur is someone who:',
       'options'  => ['a'=>'Works for a salary','b'=>'Takes risks to start and manage a business','c'=>'Manages a government department','d'=>'Invests in bonds only'],
       'correct'  => 'b'],
      ['question' => 'Price skimming strategy means:',
       'options'  => ['a'=>'Setting a low price to enter the market','b'=>'Keeping price constant','c'=>'Setting a high price initially then lowering it','d'=>'Pricing below competitors always'],
       'correct'  => 'c'],
    ],

  ], // end BCom

  // ══════════════════════════════════════════════════════
  // BBA
  // ══════════════════════════════════════════════════════
  'BBA' => [

    'marketing' => [
      ['question' => 'The 4 Ps of marketing are:',
       'options'  => ['a'=>'Product, Price, Place, Promotion','b'=>'People, Process, Physical, Profit','c'=>'Planning, Pricing, Placement, Publicity','d'=>'Product, Profit, Place, People'],
       'correct'  => 'a'],
      ['question' => 'Market segmentation means:',
       'options'  => ['a'=>'Selling to everyone equally','b'=>'Dividing the market into distinct groups with similar needs','c'=>'Reducing the product range','d'=>'Increasing the price for different customers'],
       'correct'  => 'b'],
      ['question' => 'A brand is:',
       'options'  => ['a'=>'Just a logo','b'=>'A name, term, sign or symbol that identifies a seller\'s product','c'=>'The price of a product','d'=>'A distribution channel'],
       'correct'  => 'b'],
      ['question' => 'Which promotional tool involves direct communication with potential customers?',
       'options'  => ['a'=>'Advertising','b'=>'Public Relations','c'=>'Personal Selling','d'=>'Sales Promotion'],
       'correct'  => 'c'],
      ['question' => 'A product life cycle has how many stages?',
       'options'  => ['a'=>'3','b'=>'4','c'=>'5','d'=>'6'],
       'correct'  => 'b'],
      ['question' => 'Penetration pricing involves:',
       'options'  => ['a'=>'High initial price','b'=>'Low price to gain market share quickly','c'=>'Matching competitor prices','d'=>'Pricing by customer segment'],
       'correct'  => 'b'],
      ['question' => 'CRM stands for:',
       'options'  => ['a'=>'Customer Relationship Management','b'=>'Customer Revenue Model','c'=>'Corporate Resource Management','d'=>'Client Retention Method'],
       'correct'  => 'a'],
      ['question' => 'Which of the following is an example of direct marketing?',
       'options'  => ['a'=>'Television advertising','b'=>'Billboard','c'=>'Email marketing','d'=>'Sponsorship'],
       'correct'  => 'c'],
      ['question' => 'SWOT analysis stands for:',
       'options'  => ['a'=>'Sales, Work, Operations, Trends','b'=>'Strengths, Weaknesses, Opportunities, Threats','c'=>'Strategy, Work, Objectives, Tactics','d'=>'Skills, Workforce, Output, Targets'],
       'correct'  => 'b'],
      ['question' => 'A USP (Unique Selling Proposition) is:',
       'options'  => ['a'=>'A pricing strategy','b'=>'What makes a product different from competitors','c'=>'A distribution method','d'=>'A type of advertisement'],
       'correct'  => 'b'],
    ],

    'finance' => [
      ['question' => 'Working capital management deals with:',
       'options'  => ['a'=>'Long-term investments','b'=>'Day-to-day financial operations','c'=>'Capital structure decisions','d'=>'Dividend policy'],
       'correct'  => 'b'],
      ['question' => 'NPV stands for:',
       'options'  => ['a'=>'Net Present Value','b'=>'Net Profit Value','c'=>'Nominal Present Value','d'=>'Net Production Value'],
       'correct'  => 'a'],
      ['question' => 'A positive NPV means:',
       'options'  => ['a'=>'The project should be rejected','b'=>'The project is breaking even','c'=>'The project adds value and should be accepted','d'=>'The project has no risk'],
       'correct'  => 'c'],
      ['question' => 'Leverage in finance refers to:',
       'options'  => ['a'=>'Increasing sales volume','b'=>'Using borrowed funds to increase return on investment','c'=>'Reducing costs','d'=>'Expanding the workforce'],
       'correct'  => 'b'],
      ['question' => 'Which ratio measures a company\'s ability to pay short-term obligations?',
       'options'  => ['a'=>'Debt-to-equity ratio','b'=>'Current ratio','c'=>'Return on equity','d'=>'Gross profit margin'],
       'correct'  => 'b'],
      ['question' => 'IRR stands for:',
       'options'  => ['a'=>'Internal Revenue Rate','b'=>'Investment Return Ratio','c'=>'Internal Rate of Return','d'=>'Initial Rate of Revenue'],
       'correct'  => 'c'],
      ['question' => 'A bond is:',
       'options'  => ['a'=>'A share of ownership in a company','b'=>'A debt instrument issued to raise capital','c'=>'A type of insurance','d'=>'A government subsidy'],
       'correct'  => 'b'],
      ['question' => 'Dividend is paid to:',
       'options'  => ['a'=>'Creditors','b'=>'Employees','c'=>'Shareholders','d'=>'Government'],
       'correct'  => 'c'],
      ['question' => 'The time value of money principle states:',
       'options'  => ['a'=>'Money loses value over time always','b'=>'A rupee today is worth more than a rupee tomorrow','c'=>'Interest rates are always constant','d'=>'Future cash flows are more valuable'],
       'correct'  => 'b'],
      ['question' => 'Capital budgeting is concerned with:',
       'options'  => ['a'=>'Short-term financial decisions','b'=>'Long-term investment decisions','c'=>'Daily cash management','d'=>'Employee salaries'],
       'correct'  => 'b'],
    ],

    'human resource management' => [
      ['question' => 'HRM stands for:',
       'options'  => ['a'=>'Human Resource Management','b'=>'Human Relations Method','c'=>'High Resource Management','d'=>'Human Reward Model'],
       'correct'  => 'a'],
      ['question' => 'Recruitment is the process of:',
       'options'  => ['a'=>'Firing employees','b'=>'Attracting candidates to apply for job vacancies','c'=>'Training existing employees','d'=>'Evaluating employee performance'],
       'correct'  => 'b'],
      ['question' => 'Which of the following is an internal source of recruitment?',
       'options'  => ['a'=>'Campus recruitment','b'=>'Job portals','c'=>'Promotion from within','d'=>'Placement agencies'],
       'correct'  => 'c'],
      ['question' => 'Job analysis involves:',
       'options'  => ['a'=>'Setting salaries','b'=>'Identifying duties, responsibilities and requirements of a job','c'=>'Hiring employees','d'=>'Measuring company profits'],
       'correct'  => 'b'],
      ['question' => 'Performance appraisal is done to:',
       'options'  => ['a'=>'Hire new employees','b'=>'Evaluate employee performance against set standards','c'=>'Calculate company revenue','d'=>'Set product prices'],
       'correct'  => 'b'],
      ['question' => 'Maslow\'s hierarchy of needs, from bottom to top, begins with:',
       'options'  => ['a'=>'Esteem needs','b'=>'Social needs','c'=>'Physiological needs','d'=>'Safety needs'],
       'correct'  => 'c'],
      ['question' => 'Attrition in HR refers to:',
       'options'  => ['a'=>'Increase in employee count','b'=>'Gradual reduction of workforce through resignation or retirement','c'=>'Increase in salaries','d'=>'Employee training programs'],
       'correct'  => 'b'],
      ['question' => 'A KPI in HR stands for:',
       'options'  => ['a'=>'Key Personnel Index','b'=>'Key Performance Indicator','c'=>'Knowledge and Productivity Index','d'=>'Key Process Improvement'],
       'correct'  => 'b'],
      ['question' => 'Which HR function ensures legal compliance in employment?',
       'options'  => ['a'=>'Training and Development','b'=>'Compensation Management','c'=>'Industrial Relations / Labour Law Compliance','d'=>'Recruitment'],
       'correct'  => 'c'],
      ['question' => 'Succession planning means:',
       'options'  => ['a'=>'Planning for employee vacations','b'=>'Identifying and developing employees to fill key roles in future','c'=>'Calculating future salaries','d'=>'Planning layoffs'],
       'correct'  => 'b'],
    ],

  ], // end BBA

  // ══════════════════════════════════════════════════════
  // GENERAL — fallback for any course/subject not mapped
  // ══════════════════════════════════════════════════════
  'general' => [
    'default' => [
      ['question' => 'Which of the following is a good study habit?',
       'options'  => ['a'=>'Studying for 8 hours without breaks','b'=>'Using your phone while studying','c'=>'Breaking study sessions into focused intervals','d'=>'Avoiding all revision'],
       'correct'  => 'c'],
      ['question' => 'The Pomodoro technique involves studying for:',
       'options'  => ['a'=>'10 minutes then 1 hour break','b'=>'25 minutes then a short break','c'=>'1 hour then 10 minute break','d'=>'45 minutes with no break'],
       'correct'  => 'b'],
      ['question' => 'Active recall is:',
       'options'  => ['a'=>'Reading notes repeatedly','b'=>'Highlighting text','c'=>'Testing yourself on material without looking at notes','d'=>'Watching lecture videos'],
       'correct'  => 'c'],
      ['question' => 'Spaced repetition is most effective for:',
       'options'  => ['a'=>'One-time reading','b'=>'Long-term memory retention','c'=>'Speed reading','d'=>'Group study'],
       'correct'  => 'b'],
      ['question' => 'Which environment is best for studying?',
       'options'  => ['a'=>'Noisy room with TV','b'=>'Comfortable bed','c'=>'Quiet, well-lit space with minimal distractions','d'=>'Anywhere with phone nearby'],
       'correct'  => 'c'],
      ['question' => 'Sleep is important for studying because:',
       'options'  => ['a'=>'It wastes time','b'=>'It consolidates memories','c'=>'It reduces study hours','d'=>'It has no effect on learning'],
       'correct'  => 'b'],
      ['question' => 'Which note-taking method uses a two-column format?',
       'options'  => ['a'=>'Mind mapping','b'=>'Cornell method','c'=>'Outlining','d'=>'Charting'],
       'correct'  => 'b'],
      ['question' => 'Procrastination is best overcome by:',
       'options'  => ['a'=>'Waiting for motivation','b'=>'Starting with the biggest task','c'=>'Breaking tasks into small steps and starting immediately','d'=>'Avoiding deadlines'],
       'correct'  => 'c'],
      ['question' => 'Which of the following improves focus during study?',
       'options'  => ['a'=>'Social media notifications on','b'=>'Background music with lyrics','c'=>'Phone on silent in another room','d'=>'Studying in bed'],
       'correct'  => 'c'],
      ['question' => 'Teaching someone else what you learned:',
       'options'  => ['a'=>'Wastes your time','b'=>'Has no effect on your own learning','c'=>'Is one of the most effective ways to reinforce learning','d'=>'Only helps the other person'],
       'correct'  => 'c'],
    ]
  ]

]; // end $QUESTION_BANK

/**
 * Get questions for a user's course and subject name.
 * Falls back to general → default if no match found.
 */
function getQuestions(string $course, string $subject_name, int $count = 10): array {
    global $QUESTION_BANK;

    $subject_slug = strtolower(trim($subject_name));

    // Try exact course + subject
    if (isset($QUESTION_BANK[$course][$subject_slug])) {
        $pool = $QUESTION_BANK[$course][$subject_slug];
        shuffle($pool);
        return array_slice($pool, 0, $count);
    }

    // Try partial subject match within course
    if (isset($QUESTION_BANK[$course])) {
        foreach ($QUESTION_BANK[$course] as $key => $questions) {
            if (str_contains($subject_slug, $key) || str_contains($key, $subject_slug)) {
                $pool = $questions;
                shuffle($pool);
                return array_slice($pool, 0, $count);
            }
        }
    }

    // Try general fallback
    $pool = $QUESTION_BANK['general']['default'];
    shuffle($pool);
    return array_slice($pool, 0, $count);
}