<?php

// GLOBAL variables
$print = (int)$_GET['print'] == 1;
$page = $_GET['page'] != NULL ? (int)$_GET['page'] : 'ALL';
$imgs = !$print;
if($_GET['imgs'] != NULL) {
	$imgs = ((int)$_GET['imgs'] == 1);
}

// STATIC FINAL variables
$EVEN = 0;
$ODD = 1;

function printBackToTop() {
  global $print;
  if(!$print) {
    echo("<br />(<a href=\"#top\">top</a>)");
  }
}

function strMatch($needle, $haystack) {
  return preg_match("/^(?:\t)?$needle/", $haystack);
}


function strReplace($needle, $haystack) {
  return preg_replace("/^(?:\t)?$needle/", "", $haystack);
}

class Title {
  var $title;
  var $comments = array();
  var $currComment;

  function Title($t) {
    $this->title = $t;
  }
}

class Job {
  var $hr;
  var $duration;
  var $group;
  var $position;
  var $titles = array();
  var $images = array();
  var $currTitle;

  function toHTML($imgs) {
    echo getTabs(4) . "<!-- " . rtrim($this->group) . " -->";

	// check if HR should be written
	if($this->hr) {
      if(!$imgs) echo getTabs(4) . "<br />";
	  echo getTabs(4) . "<hr />";
	}

    // check if duration is provided (optional)
    $tmp = rtrim($this->duration);
    if(strlen($tmp) > 0) {
      echo getTabs(4) . "<span class=\"companyDate\">" . rtrim($this->duration) . "</span><br />";
    }

    // check if group is provided (optional)
    $tmp = rtrim($this->group);
    if(strlen($tmp) > 0) {
      echo getTabs(4) . "<span class=\"companyName\">" . rtrim($this->group) . "</span><br />";
    }

	// check if position is provided (optional)
    $tmp = rtrim($this->position);
    if(strlen($tmp) > 0) {
      echo getTabs(4) . "<span class=\"companyPosition\">" . rtrim($this->position) . "</span><br />";
    }

    // print title
    echo getTabs(4) . "<ul class=\"workListGroup\">";
    for($i=0; $i<count($this->titles); $i++) {
      echo getTabs(5) . "<li>";
      echo getTabs(6) . rtrim($this->titles[$i]->title) . "<br />";

      // print comments
      $count = count($this->titles[$i]->comments);
      if($count) {
        for($j=0; $j<$count; $j++) {
          echo getTabs(6) . "<i>" . rtrim($this->titles[$i]->comments[$j]) . "</i><br />";
        }
      }
      echo getTabs(5) . "</li>";
    }
    echo getTabs(4) . "</ul>";
    
    // print images
    $count = count($this->images);
    if($count && $imgs) {
      echo getTabs(4) . "<br />";
      for($i=0; $i<$count; $i++) {
        echo getTabs(4) . "<img class=\"imageBorder\" src=\"" . rtrim($this->images[$i]) . "\" />";
      }
      echo getTabs(4) . "<br />";
    }
    
    
    if($imgs) echo getTabs(4) . "<br />";
    echo getTabs(3);
  }
}

function getTabs($number) {
  $tabs = "\n";
  while($number-- > 0) {
    $tabs .= "  "; // \t
  }
  return $tabs;
}

function printSingleLineContent($filename) {
  $file = fopen($filename, "r");
  if($file) {
    while(!feof($file)) {
      $line = fgets($file);
      if(strlen($line) > 0) {
        echo "$line<br />";
      }
    }
    fclose($file);
  }
}

function printSingleColumnContent($filename) {
  $file = fopen($filename, "r");
  if($file) {
    echo getTabs(4) . "<ul>";
    while(!feof($file)) {
      $line = fgets($file);
      if(strlen($line) > 0) {
        echo getTabs(5) . "<li>" . rtrim($line) . "</li>";
      }
    }
    fclose($file);
    echo getTabs(4) . "</ul>";
    echo getTabs(3);
  }
}

function printDoubleColumnContent($filename, $evenOrOdd) {
  $count = 0;
  $file = fopen($filename, "r");
  if($file) {
    echo getTabs(5) . "<ul>";
    while(!feof($file)) {
      $line = fgets($file);
      if(strlen($line) > 0 && $count%2 == $evenOrOdd) {
        echo getTabs(6) . "<li>" . rtrim($line) . "</li>";
      }
      $count++;
    }
    fclose($file);
    echo getTabs(5) . "</ul>";
    echo getTabs(4);
  }
}

function printExperiences($filename) {
  global $imgs;

  $regexBlockEnd = "---";
  $regexNeedles = array("hr" => "\[HR\]", "duration" => "\[DUR\] ", "group" => "\[GRP\] ", "position" => "\[POS\] ", "title" => "\* ", "comment" => "\+ ", "image" => "\- ");
  $file = fopen($filename, "r");

  if($file) {
    $jobs = array();

    // read until eof
    while(!feof($file)) {
      // get 1 line at a time
      $line = fgets($file);
      // check for block end
      if(strMatch($regexBlockEnd, $line)) {
        // block ended output html here
        if($job) {
          array_push($jobs, $job);
          $job = null;
        }
      } else if(strlen($line)) {
		if(strMatch($regexNeedles['hr'], $line)) {
		  if(!$job) $job = new Job;
          $job->hr = true;
        } else if(strMatch($regexNeedles['duration'], $line)) {
          if(!$job) $job = new Job;
          $job->duration = strReplace($regexNeedles['duration'], $line);
        } else if(strMatch($regexNeedles['group'], $line)) {
          if(!$job) $job = new Job;
          $job->group = strReplace($regexNeedles['group'], $line);
		} else if(strMatch($regexNeedles['position'], $line)) {
		  if(!$job) $job = new Job;
          $job->position = strReplace($regexNeedles['position'], $line);
        } else if(strMatch($regexNeedles['title'], $line)) {
          if($job) {
            $job->currTitle = new Title(strReplace($regexNeedles['title'], $line));
            array_push($job->titles, $job->currTitle);
          }
        } else if(strMatch($regexNeedles['comment'], $line)) {
          if($job) {
            $job->currTitle->currComment = strReplace($regexNeedles['comment'], $line);
            array_push($job->currTitle->comments, $job->currTitle->currComment);
          }
        } else if(strMatch($regexNeedles['image'], $line)) {
          if($job) {
            array_push($job->images, strReplace($regexNeedles['image'], $line));
          }
        }
      }
    }
    fclose($file);

    // print out jobs
    for($i=0; $i<count($jobs); $i++) {
      echo $jobs[$i]->toHTML($imgs);
    }
  }
}

/*

    <div class="sidebarLink"><a href="#iphone_exp"><span class="sidebarLinkText">iphone</span>. 05</a></div>
    <div class="sidebarLink"><a href="#android_exp"><span class="sidebarLinkText">android</span>. 06</a></div>
    <div class="sidebarLink"><a href="#j2me_exp"><span class="sidebarLinkText">j2me, brew</span>. 07</a></div>
    <div class="sidebarLink"><a href="#web_exp"><span class="sidebarLinkText">web, server</span>. 08</a></div>
    <div class="sidebarLink"><a href="#j2se_exp"><span class="sidebarLinkText">j2se</span>. 09</a></div>
    <div class="sidebarLink"><a href="#projects"><span class="sidebarLinkText">projects</span>. 10</a></div>


<?php if($page == '3' || $page == 'ALL') { ?>
      <!-- iphone experience -->
      <a href="iphone_exp" id="iphone_exp"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">IPHONE<br />EXPERIENCE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/05_iphone.txt"); ?></div>
<?php } ?>

<?php if($page == '4' || $page == 'ALL') { ?>
      <!-- android experience -->
      <a href="android_exp" id="android_exp"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">ANDROID<br />EXPERIENCE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/06_android.txt"); ?></div>
<?php } ?>

<?php if($page == '5' || $page == 'ALL') { ?>
      <!-- j2me experience -->
      <a href="j2me_exp" id="j2me_exp"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">J2ME &amp; BREW<br />EXPERIENCE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/07_j2me.txt"); ?></div>
<?php } ?>

<?php if($page == '6' || $page == 'ALL') { ?>
      <!-- server and web experience -->
      <a href="web_exp" id="web_exp"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">WEB &amp; SERVER<br />EXPERIENCE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/08_web.txt"); ?></div>
<?php } ?>

<?php if($page == '7' || $page == 'ALL') { ?>
      <!-- application experience -->
      <a href="j2se_exp" id="j2se_exp"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">J2SE<br />EXPERIENCE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/09_j2se.txt"); ?></div>
<?php } ?>

<?php if($page == '8' || $page == 'ALL') { ?>
      <!-- misc projects -->
      <a href="projects" id="projects"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">MISCELLANEOUS<br />PROJECTS<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/10_misc.txt"); ?></div>
<?php } ?>

*/

?>

<?php
$job_title = "software engineer"
?>

<html>
<head>
<title>Danny Yu, <?php echo $job_title; ?></title>
<link rel="stylesheet" href="index.css" type="text/css">
<?php if($print) { ?>
<link rel="stylesheet" href="printable.css" type="text/css">
<?php } ?>
</head>
<body>
<a href="top" id="top"></a>
<div id="wrap">

<?php if(!$print) { ?>

  <div id="sidebar">
    <div id="sidebarTitle">dyu</div>
    <div id="sidebarTitleSubtext"><?php echo $job_title; ?></div>
    <div class="sidebarLink"><a href="pdf/resume-danny-yu.pdf"><span class="sidebarLinkText"><img src="images/pdf-icon.png"/>&nbsp; pdf resume</span>. 00</a></div>
    <div class="sidebarLink"><a href="#contact"><span class="sidebarLinkText">contact</span>. 01</a></div>
    <div class="sidebarLink"><a href="#education"><span class="sidebarLinkText">education</span>. 02</a></div>
    <div class="sidebarLink"><a href="#expertise"><span class="sidebarLinkText">expertise</span>. 03</a></div>
    <div class="sidebarLink"><a href="#work_exp"><span class="sidebarLinkText">experience</span>. 04</a></div>
    <div class="sidebarLink"><a href="#dev_exp"><span class="sidebarLinkText">products</span>. 05</a></div>
    <div class="sidebarLink"><a href="#certs_awards"><span class="sidebarLinkText">certifications</span>. 06</a></div>
    <div class="sidebarLink"><a href="#aboutme"><span class="sidebarLinkText">about me</span>. 07</a></div>
    <div class="sidebarSpacer"></div>
  </div>

<?php } else { ?>

  <div id="sidebarContainer">
    <div id="sidebarTitle">dyu</div>
    <div id="sidebarTitleSubtext"><?php echo $job_title; ?></div>
  </div>
  <div id="resumeContact"><span class="fontsize40"><b>Danny Yu</b></span><br /><a href="mailto:wdyu2002@yahoo.com">wdyu2002@yahoo.com</a></div>
  <div class="clearBoth"></div>

<?php } ?>

  <div id="content">
    <div id="resume">

<?php if(!$print) { ?>
      <!-- contact info -->
      <a href="contact" id="contact"></a>
      <div id="resumeContact"><span class="fontsize40"><b>Danny Yu</b></span><br /><a href="mailto:wdyu2002@yahoo.com">wdyu2002@yahoo.com</a></div>
<?php } ?>

<?php if($page == '1' || $page == 'ALL') { ?>
      <!-- education -->
      <a href="education" id="education"></a>
      <div class="resumeDivider"></div>
      <div class ="titleColumnLeft">EDUCATION<?php printBackToTop(); ?></div>
      <div class ="contentColumnRight">University of California, San Diego<br /><b>Bachelor of Science in Electrical & Computer Engineering</b></div>

      <!-- expertise -->
      <a href="expertise" id="expertise"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">EXPERTISE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printSingleColumnContent("data/01_expertise.txt"); ?></div>
      <div class="clearRight"><br /></div>

      <!-- programming api -->
      <div class="titleColumnLeft"></div>
      <div class="contentColumnRight"><b>Familiar Programming Languages and APIs</b><br /><br />
        <div class="splitColumnLeft"><?php printDoubleColumnContent("data/02_apis.txt", $EVEN); ?></div>
        <div class="splitColumnRight"><?php printDoubleColumnContent("data/02_apis.txt", $ODD); ?></div>
      </div>
      <div class="clearRight"><br /></div>

      <!-- software experience -->
      <div class="titleColumnLeft"></div>
      <div class="contentColumnRight"><b>Software Experience</b><br /><br />
        <div class="splitColumnLeft"><?php printDoubleColumnContent("data/03_software.txt", $EVEN); ?></div>
        <div class="splitColumnRight"><?php printDoubleColumnContent("data/03_software.txt", $ODD); ?></div>
      </div>
      <div class="clearRight"></div>
<?php } ?>

<?php if($page == '2' || $page == 'ALL') { ?>
      <!-- work experience -->
      <a href="work_exp" id="work_exp"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">WORK<br />EXPERIENCE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/04_work.txt"); ?></div>
<?php } ?>

<?php if($page == '3' || $page == 'ALL') { ?>
      <!-- iphone experience -->
      <a href="dev_exp" id="dev_exp"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">DEVELOPMENT<br />EXPERIENCE<?php printBackToTop(); ?></div>
      <div class="contentColumnRight"><?php printExperiences("data/05_developed.txt"); ?></div>
<?php } ?>

      <!-- insert removed pieces here -->

<?php if($page == '4' || $page == 'ALL') { ?>
      <!-- certifications and awards -->
      <a href="certs_awards" id="certs_awards"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">CERTIFICATIONS<br />AND AWARDS<?php printBackToTop(); ?></div>
      <div class="contentColumnRight">
        <ul>
          <li>
            <b>Certifications</b><br />
            <i class="workListSubtext"><?php printSingleLineContent("data/11_certifications.txt"); ?></i>
          </li>
          <br />
          <li class="workListText">
            <b>Awards</b><br />
            <i class="workListSubtext"><?php printSingleLineContent("data/12_awards.txt"); ?></i>
          </li>
        </ul>
      </div>

      <!-- about me -->
      <a href="aboutme" id="aboutme"></a>
      <div class="resumeDivider"></div>
      <div class="titleColumnLeft">ABOUT ME<?php printBackToTop(); ?></div>
      <div class="contentColumnRight">
        <div class="splitColumnLeft"><?php printDoubleColumnContent("data/13_skills.txt", $EVEN); ?></div>
        <div class="splitColumnRight"><?php printDoubleColumnContent("data/13_skills.txt", $ODD); ?></div>
      </div>
      <div class="clearRight"><br /></div>

      <!-- hobbies -->
      <div class="titleColumnLeft"></div>
      <div class="contentColumnRight"><b>Hobbies and Interests</b><br /><br />
        <div class="splitColumnLeft"><?php printDoubleColumnContent("data/14_hobbies.txt", $EVEN); ?></div>
        <div class="splitColumnRight"><?php printDoubleColumnContent("data/14_hobbies.txt", $ODD); ?></div>
      </div>
      <div class="clearRight"></div>
      <br />
      <br />
<?php } ?>

      <!-- footer clears both -->
    </div>
    <!-- end resume -->
  </div>
  <!-- end content -->
</div>
<!-- end wrap -->

</body>
</html>
