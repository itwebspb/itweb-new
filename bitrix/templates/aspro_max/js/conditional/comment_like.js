document.addEventListener("click", function (event) {
  const target = event.target.closest(".rating-vote__action:not(.disable)");
  if (!target) {
    return;
  }

  const parent = target.closest(".rating-vote");

  target.classList.toggle("active");

  BX.ajax
    .runAction(`${arAsproOptions.SOLUTION_ID.replace(".", ":")}.CommentLike.vote`, {
      data: { commentId: parent.dataset.comment_id, action: target.dataset.action },
    })
    .then(function (response) {
      const data = response.data || {};

      if (data.LIKE !== undefined) {
        parent.querySelector(".rating-vote__action-result--like").textContent = data.LIKE;
      }
      if (data.DISLIKE !== undefined) {
        parent.querySelector(".rating-vote__action-result--dislike").textContent = data.DISLIKE;
      }
      if (data.SET_ACTIVE_LIKE !== undefined) {
        parent.querySelector(".rating-vote__action--like").classList.toggle("active", data.SET_ACTIVE_LIKE);
      }
      if (data.SET_ACTIVE_DISLIKE !== undefined) {
        parent.querySelector(".rating-vote__action--dislike").classList.toggle("active", data.SET_ACTIVE_DISLIKE);
      }
    });
});
