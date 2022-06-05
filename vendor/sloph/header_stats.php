<?
    $now = new DateTime();
    $from = new DateTime($now->format("Y-m-01"));
    $to = new DateTime($now->format("Y-m-t"));
    $month_posts = get_posts($ep, $from->format(DATE_ATOM), $to->format(DATE_ATOM));

    $locations = get_locations($ep);
    if($locations != null){
      $locations = $locations->toRdfPhp();
    }
    $tags = get_tags($ep);

    $last_checkin = construct_last_of_type($ep, "as:Arrive");
    $checkin_summary = make_checkin_summary($last_checkin, $locations);

    $consume_stats = stat_box($ep, "consume");
    $exercise_stats = stat_box($ep, "exercise");
    $budget_stats = stat_box($ep, "budget", $month_posts);
    $words_stats = stat_box($ep, "words", $month_posts);

    $project_icons = get_project_icons($ep);

    /* Views stuff */
    $resource->addLiteral('view:stylesheet', "views/home.css");
?>