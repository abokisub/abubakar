import { forwardRef } from 'react';
import PropTypes from 'prop-types';
import { Box } from '@mui/material';

const Carousel = forwardRef(({ children, ...other }, ref) => {
  return (
    <Box ref={ref} {...other}>
      {children}
    </Box>
  );
});

Carousel.propTypes = {
  children: PropTypes.node,
};

export default Carousel; 